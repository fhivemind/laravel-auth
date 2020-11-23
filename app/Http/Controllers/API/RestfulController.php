<?php

namespace App\Http\Controllers;

use App\Models\RestfulModel;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\QueryBuilderRequest;
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

        // If we are caching the endpoint, do a simple get all resources
        // Only allowed for empty request
        if (static::$cacheAll && count($request->all()) === 0) {
            return $this->response->collection(Cache::remember(static::getCacheKey(), static::$cacheExpiresIn, function () use ($model) {
                $query = QueryBuilder::for($model::with($model->getAuthorizedWith()));

                return $query->get();
            }), $this->getTransformer());
        }

        // Create query from request
        $query = static::newQueryFromRequest($request, $model);
        
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
        $model = $this->getModelByIdFromRequest($id, $request);

        if (! $model) {
            throw new NotFoundHttpException('Resource \'' . class_basename(static::model()) . '\' with given ID ' . $id . ' not found');
        }

        $this->authorizeUserAction('view', $model);

        return $this->response->item($model, $this->getTransformer());
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

        $resource = static::reloadModelFromRequest($resource, $request);

        return $this->response->item($resource, $this->getTransformer())->setStatusCode(201);
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
        $model = static::model()::find($id);

        if (! $model) {
            // Doesn't exist - create
            return $this->post($request);
        } else {
            // Exists - update
            return $this->update($model, $request);
        }
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
        
        // delegate to update function
        return $this->update($model, $request);
    }

    /**
     * Internally update a specified resource. We delegate calls to this function
     * as we don't want to copy same code multiple times for PUT and PATCH.
     *
     * @param model $model model to update
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    protected function update($model, Request $request)
    {
        $this->authorizeUserAction('update', $model);

        $this->restfulService->validateResourceUpdate($model, $request->input());

        $resource = $this->restfulService->persistResource($model->fill($request->input()));

        $resource = static::reloadModelFromRequest($resource, $request);

        return $this->response->item($resource, $this->getTransformer())->setStatusCode(200);
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
     * Retrieves model from db based on request. Uses authorized data in request.
     * Supports advanced query configuration via requests.
     *
     * @param string $id
     * @param Request $request
     * 
     * @throws Exception
     * @return Model
     */
    public function getModelByIdFromRequest($id, Request $request) {
        return static::newQueryFromRequest($request, $this->model, [$this->model->getKeyName() => $id])->first();
    }

    /**
     * Processes an edited version of a model if the request asks so.
     * If the request is empty, returns passed object to avoid duplicating calls
     * to obtain same data.
     *
     * @param Model $model
     * @param Request $request
     * 
     * @throws Exception
     * @return Model
     */
    public static function reloadModelFromRequest(RestfulModel $model, Request $request) {
        // don't trigger extra db call if request not asking for it
        if (count($request->all()) === 0)
            return $model;

        // otherwise, get model from request
        return static::newQueryFromRequest($request, $model, [$model->getKeyName() => $model->getKey()])->first();
    }

    /**
     * Returns a query for a model based on provided Request and search parameters.
     * In case of RestfulModel, the query will also add additional fields such as filtering, selecting...
     *
     * @param Request $request
     * @param Model $model
     * @param array $search
     * 
     * @throws Exception
     * @return QueryBuilder
     */
    public static function newQueryFromRequest(Request $request, $model, $search = [], $relations = [])
    {
        // Handle RestfulModel logic
        if ($model instanceof RestfulModel) {

            // Authorize query
            static::authorizeUserRequestOnModel($request, $model);

            // Check relations
            if (empty($relations)) {
                $relations = $model->getAuthorizedWith();
            }
        
            // Create query
            $query = QueryBuilder::for($model::with($relations), $request);

            // Append search parameters
            if (count($search)) {
                $queryAttrs = $model->getAuthorizedQueryFields();
                foreach($search as $key => $value) {
                    if (in_array($key, $queryAttrs)) {
                        $query->where($key, $value);
                    }
                }
            }

            // Add allowed request parameters
            $query = $query
                ->allowedAppends($model->getAuthorizedQueryAppends())
                ->allowedFilters($model->getAuthorizedQueryFilters())
                ->allowedSorts($model->getAuthorizedQuerySorts())
                ->allowedFields($model->getAuthorizedQueryFields())
                ->allowedIncludes($model->getAuthorizedQueryIncludes());

            return $query;
        }

        // otherwise, generate default query builder
        return QueryBuilder::for($model::with($relations), $request);
    }
}
