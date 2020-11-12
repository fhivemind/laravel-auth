<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Cache;

abstract class RestfulController extends BaseRestfulController
{
    /**
     * Cache key for getAll() function - all of a collection's resources
     */
    const CACHE_KEY_GET_ALL = '%s_getAll';

    /**
     * @var bool Cache setting for collection / getAll endpoint
     *
     * If enabled, the get collection endpoint is cached - but NOTE that this will bypass specific user qualification
     * of the query, and any other query params - such as pagination. It can be used for SIMPLE resources that
     * change infrequently
     */
    public static $cacheAll = false;

    /**
     * @var int Cache expiry timeout (24 hours by default)
     */
    public static $cacheExpiresIn = 86400;

    /**
     * Request to retrieve a collection of all items of this resource
     *
     * @return \Dingo\Api\Http\Response
     */
    public function getAll(Request $request)
    {
        $this->authorizeUserAction('viewAll');

        $model = $this->model;

        // If we are caching the endpont, do a simple get all resources
        // Only allowed for empty request
        if (static::$cacheAll && count($request->all()) === 0) {
            return $this->response->collection(Cache::remember(static::getCacheKey(), static::$cacheExpiresIn, function () use ($model) {
                $query = QueryBuilder::for($model::with($model::getItemWith()));

                return $query->get();
            }), $this->getTransformer());
        }

        // Create query from request
        $query = static::requestQuery($request);
        
        // Validate query
        $this->qualifyCollectionQuery($query);

        // Handle pagination, if applicable
        $perPage = $model->getPerPage();
        if ($perPage) {
            // If specified, use per_page of the request
            if (request()->has('per_page')) {
                $perPage = intval(request()->input('per_page'));
            }

            $paginator = $query->paginate($perPage)->appends(request()->query());;

            return $this->response->paginator($paginator, $this->getTransformer());
        } else {
            $resources = $query->get();

            return $this->response->collection($resources, $this->getTransformer());
        }
    }

    /**
     * Request to retrieve a single item of this resource
     *
     * @param string $uuid UUID of the resource
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function get($uuid, Request $request)
    {
        // Create query from request
        $resource = static::requestQuery($request, [$this->model->getKeyName() => $uuid])->first();

        if (! $resource) {
            throw new NotFoundHttpException('Resource \'' . class_basename(static::model()) . '\' with given UUID ' . $uuid . ' not found');
        }

        $this->authorizeUserAction('view', $resource);

        return $this->response->item($resource, $this->getTransformer());
    }

    /**
     * Request to create a new resource
     *
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws HttpException|QueryException
     */
    public function post(Request $request)
    {
        $this->authorizeUserAction('create');

        $model = $this->model;

        $this->restfulService->validateResource($model, $request->input());

        $resource = $this->restfulService->persistResource(new $model($request->input()));

        // Retrieve full model
        $resource = static::requestQuery($request, [$this->model->getKeyName() => $resource->getKey()])->first();

        if ($this->shouldTransform()) {
            $response = $this->response->item($resource, $this->getTransformer())->setStatusCode(201);
        } else {
            $response = $resource;
        }

        return $response;
    }

    /**
     * Request to create or replace a resource
     *
     * @param Request $request
     * @param string $uuid
     * @return \Dingo\Api\Http\Response
     */
    public function put(Request $request, $uuid)
    {
        $model = static::model()::find($uuid);

        if (! $model) {
            // Doesn't exist - create
            $this->authorizeUserAction('create');

            $model = $this->model;

            $this->restfulService->validateResource($model, $request->input());
            $resource = $this->restfulService->persistResource(new $model($request->input()));

            $resource->loadMissing($model::getItemWith());

            if ($this->shouldTransform()) {
                $response = $this->response->item($resource, $this->getTransformer())->setStatusCode(201);
            } else {
                $response = $resource;
            }
        } else {
            // Exists - replace
            $this->authorizeUserAction('update', $model);

            $this->restfulService->validateResourceUpdate($model, $request->input());
            $this->restfulService->persistResource($model->fill($request->input()));

            if ($this->shouldTransform()) {
                $response = $this->response->item($model, $this->getTransformer())->setStatusCode(200);
            } else {
                $response = $model;
            }
        }

        return $response;
    }

    /**
     * Request to update the specified resource
     *
     * @param string $uuid UUID of the resource
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function patch($uuid, Request $request)
    {
        $model = static::model()::findOrFail($uuid);

        $this->authorizeUserAction('update', $model);

        $this->restfulService->validateResourceUpdate($model, $request->input());

        $this->restfulService->patch($model, $request->input());

        if ($this->shouldTransform()) {
            $response = $this->response->item($model, $this->getTransformer());
        } else {
            $response = $model;
        }

        return $response;
    }

    /**
     * Deletes a resource by UUID
     *
     * @param string $uuid UUID of the resource
     * @return \Dingo\Api\Http\Response
     * @throws NotFoundHttpException
     */
    public function delete($uuid)
    {
        $model = static::model()::findOrFail($uuid);

        $this->authorizeUserAction('delete', $model);

        $deletedCount = $model->delete();

        if ($deletedCount < 1) {
            throw new NotFoundHttpException('Could not find a resource with that UUID to delete');
        }

        return $this->response->noContent()->setStatusCode(204);
    }

    /**
     * Get the cache key for a given endpoint in this controller
     *
     * @param string $endpoint
     * @return string $cacheKey
     */
    public static function getCacheKey(string $endpoint = 'getAll'): ?string
    {
        if ($endpoint == 'getAll') {
            return sprintf(static::CACHE_KEY_GET_ALL, static::model());
        }

        return null;
    }

    /**
     * Builds a query based on provided Request and search parameters.
     *
     * @param Request $request
     * @param array $search
     * @return QueryBuilder
     */
    public static function requestQuery(Request $request, $search = [])
    {
        // Initialize model
        $name = static::model();
        $model = new $name;

        // Create query
        $query = QueryBuilder::for($model::with($model::getItemWith()), $request);

        // Append search parameters
        if (count($search)) {
            foreach($search as $key => $value) {
                if (in_array($key, static::repository()::getFieldsSearchable())) {
                    $query->where($key, $value);
                }
            }
        }

        // Append request data
        $query = $query
            ->allowedFilters($model->getAllowedFilters())
            ->allowedSorts($model->getAllowedSorts())
            ->allowedFields($model->getAllowedFields())
            ->allowedIncludes($model->getAllowedIncludes());

        return $query;
    }
}
