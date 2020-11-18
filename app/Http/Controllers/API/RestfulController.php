<?php

namespace App\Http\Controllers;

use App\Models\RestfulModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Services\RestfulService;
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
                $query = QueryBuilder::for($model::with($model->getAllowedWith()));

                return $query->get();
            }), $this->getTransformer());
        }

        // Create query from request
        $query = static::requestQuery($request, $model);
        
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
     * @param string $id ID of the resource
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function get($id, Request $request)
    {
        // Create query from request
        $resource = static::requestQuery($request, $this->model, [$this->model->getKeyName() => $id])->first();

        if (! $resource) {
            throw new NotFoundHttpException('Resource \'' . class_basename(static::model()) . '\' with given ID ' . $id . ' not found');
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
        $resource = static::requestQuery($request, $this->model, [$this->model->getKeyName() => $resource->getKey()])->first();

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
     * @param string $id
     * @return \Dingo\Api\Http\Response
     */
    public function put(Request $request, $id)
    {
        $model = static::requestQuery($request, $this->model, [$this->model->getKeyName() => $id])->first();

        if (! $model) {
            // Doesn't exist - create
            $this->authorizeUserAction('create');

            $model = $this->model;

            $this->restfulService->validateResource($model, $request->input());
            $resource = $this->restfulService->persistResource(new $model($request->input()));

            $resource->loadMissing($model->getAllowedWith());

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
     * @param string $id ID of the resource
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function patch($id, Request $request)
    {
        $model = static::model()::findOrFail($id);

        $this->authorizeUserAction('update', $model);

        $this->restfulService->validateResourceUpdate($model, $request->input());
        $this->restfulService->persistResource($model->fill($request->input()));

        if ($this->shouldTransform()) {
            $response = $this->response->item($model, $this->getTransformer());
        } else {
            $response = $model;
        }

        return $response;
    }

    /**
     * Deletes a resource by ID
     *
     * @param string $id ID of the resource
     * @return \Dingo\Api\Http\Response
     * @throws NotFoundHttpException
     */
    public function delete($id)
    {
        $model = static::model()::findOrFail($id);

        $this->authorizeUserAction('delete', $model);

        $deletedCount = $model->delete();

        if ($deletedCount < 1) {
            throw new NotFoundHttpException('Could not find a resource with that ID to delete');
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
    public static function requestQuery(Request $request, RestfulModel $model, $search = [])
    {
        // Create query
        $query = QueryBuilder::for($model::with($model->getAllowedWith()), $request);

        // Append search parameters
        if (count($search)) {
            $res = $model->getAllowedSelects();
            foreach($search as $key => $value) {
                if (in_array($key, $res)) {
                    $query->where($key, $value);
                }
            }
        }

        // Append request data
        $filters = $model->getAllowedFilters();
        $query = $query
            ->allowedFilters($filters)
            ->allowedSorts($model->getAllowedSorts())
            ->allowedFields($model->getAllowedSelects())
            ->allowedIncludes($model->getAllowedIncludes())
            ->allowedAppends($model->getAllowedAppends());

        return $query;
    }
}
