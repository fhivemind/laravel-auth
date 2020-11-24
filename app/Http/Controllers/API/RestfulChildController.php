<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\RestfulService;
use App\Helpers;

abstract class RestfulChildController extends BaseRestfulController
{
    /**
    * @var \App\Services\RestfulService|null
    */
    protected $parentRepository = null;

    /**
     *  @var \App\Models\RestfulModel|null
     */
    protected $parentModel = null;

    /**
     * Specify the parent repository that should be associated with this controller. This is the parent repository of the
     * primary repository the controller deals with.
     *
     * @return string
     */
    abstract public static function parentRepository();

    /**
     * Returns parent model associated with this Controller (parent repository).
     * 
     * @return string
     */
    public static function parentModel()
    {
        if (! is_null(static::parentRepository())) {
            return static::parentRepository()::model();
        }
    }

    /**
     * RestfulChildController constructor.
     *
     * @param RestfulService $restfulService
     */
    public function __construct(RestfulService $restfulService)
    {
        parent::__construct($restfulService);

        $this->parentRepository = static::makeRepository(static::parentRepository());
        $this->parentModel = static::makeModel(static::parentModel());
    }

    /**
     * These are the abilities which the authenticated user must be able to perform on the parent model
     * in order to perform the relevant action on the child model of this controller (array keys).
     *
     * Array keys are child resource operations, and values are matching abilities on parent model required
     *
     * Create means create a new
     *
     * @var array
     */
    public $parentAbilitiesRequired = [
        'create'    => 'update',
        'view'      => 'view',
        'viewAll'   => 'view',
        'update'    => 'update',
        'delete'    => 'delete',
    ];

    /**
     * Request to retrieve a collection of all items owned by the parent of this resource
     *
     * @param string $id
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function getAll($id, Request $request)
    {
        $this->authorizeUserAction('viewAll');

        $parentModel = static::parentModel();
        $parentResource = $parentModel::findOrFail($id);

        // Authorize ability to view children models for parent
        $this->authorizeUserAction($this->parentAbilitiesRequired['view'], $parentResource);

        $model = static::model();
        $resourceRelationName = Helpers::modelRelationName($model);

        // Form model's with relations for parent query
        $withArray = [];
        foreach ($this->model->getAuthorizedWith() as $modelRelation) {
            $withArray[] = $resourceRelationName . '.' . $modelRelation;
        }

        $withArray = array_merge([$resourceRelationName], $withArray);

        $parentResource = $parentResource->where($parentResource->getKeyName(), '=', $parentResource->getKey())->with($withArray)->first();

        $collection = $parentResource->getRelationValue($resourceRelationName);

        if ($collection == null) {
            $collection = new Collection();
        }

        return $this->response->collection($collection, $this->getTransformer());
    }

    /**
     * Request to retrieve a single child owned by the parent of this resource (hasOne relation)
     *
     * @param string $id
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function getOneFromParent($id, Request $request)
    {
        $parentModel = static::parentModel();
        $parentResource = $parentModel::findOrFail($id);

        // Authorize ability to view children models for parent
        $this->authorizeUserAction($this->parentAbilitiesRequired['view'], $parentResource);

        $model = static::model();
        $resourceRelationName = Helpers::modelRelationName($model, 'one');

        // Form model's with relations for parent query
        $withArray = [];
        foreach ($this->model->getAuthorizedWith() as $modelRelation) {
            $withArray[] = $resourceRelationName . '.' . $modelRelation;
        }

        $withArray = array_merge([$resourceRelationName], $withArray);
        $parentResource = $parentResource->where($parentResource->getKeyName(), '=', $parentResource->getKey())->with($withArray)->first();

        $resource = $parentResource->getRelationValue($resourceRelationName);

        // Make sure it exists, and if so, authorize view action
        if ($resource == null) {
            throw new NotFoundHttpException('Can not find a "' . $resourceRelationName . '" attached to this ' . (new \ReflectionClass($parentModel))->getShortName());
        } else {
            // Authorize ability to view this model
            $this->authorizeUserAction('view', $resource);
        }

        return $this->response->item($resource, $this->getTransformer());
    }

    /**
     * Request to retrieve a single item of this resource
     *
     * @param string $parentId ID of the parent resource
     * @param string $id ID of the resource
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function get($parentId, $id)
    {
        // Check parent exists
        $parentModel = static::parentModel();
        $parentResource = $parentModel::findOrFail($parentId);

        // Authorize ability to view children model for parent
        $this->authorizeUserAction($this->parentAbilitiesRequired['view'], $parentResource);

        // Get resource
        $model = $this->model;
        $resource = $model::with($model->getAuthorizedWith())->where($model->getKeyName(), '=', $id)->firstOrFail();

        // Check resource belongs to parent
        if ($resource->getAttribute(($parentResource->getKeyName())) != $parentResource->getKey()) {
            throw new AccessDeniedHttpException('Resource \'' . class_basename(static::model()) . '\' with given ID ' . $id . ' does not belong to ' .
                'resource \'' . class_basename(static::parentModel()) . '\' with given ID ' . $parentId . '; ');
        }

        if (! $resource) {
            throw new NotFoundHttpException('Resource \'' . class_basename(static::model()) . '\' with given ID ' . $id . ' not found');
        }

        // Authorize ability to create this model
        $this->authorizeUserAction('view', $resource);

        return $this->response->item($resource, $this->getTransformer());
    }

    /**
     * Request to create the child resource owned by the parent resource
     *
     * @oaram string $parentId Parent's ID
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function post($parentId, Request $request)
    {
        // Check parent exists
        $parentModel = static::parentModel();
        $parentResource = $parentModel::findOrFail($parentId);

        // Authorize ability to create children model for parent
        $this->authorizeUserAction($this->parentAbilitiesRequired['create'], $parentResource);

        // Authorize ability to create this model
        $this->authorizeUserAction('create');

        $requestData = $request->input();
        $model = $this->model;

        // Validate
        $this->restfulService->validateResource($model, $requestData);

        // Set parent key in request data
        $resource = new $model($requestData);

        $parentRelation = $parentResource->{$this->getChildRelationNameForParent($parentResource, static::model())}();
        $resource->{$parentRelation->getForeignKeyName()} = $parentId;

        // Create model in DB
        $resource = $this->restfulService->persistResource($resource);

        // Retrieve full model
        $resource = $model::with($model->getAuthorizedWith())->where($model->getKeyName(), '=', $resource->getKey())->first();

        if ($this->shouldTransform()) {
            $response = $this->response->item($resource, $this->getTransformer())->setStatusCode(201);
        } else {
            $response = $resource;
        }

        return $response;
    }

    /**
     * Request to update the specified child resource
     *
     * @param string $parentId ID of the parent resource
     * @param string $id ID of the child resource
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function patch($parentId, $id, Request $request)
    {
        // Check parent exists
        $parentModel = static::parentModel();
        $parentResource = $parentModel::findOrFail($parentId);

        // Authorize ability to update children models for parent
        $this->authorizeUserAction($this->parentAbilitiesRequired['update'], $parentResource);

        // Get resource
        $model = $this->model;
        $resource = static::model()::findOrFail($id);

        // Check resource belongs to parent
        if ($resource->getAttribute(($parentResource->getKeyName())) != $parentResource->getKey()) {
            throw new AccessDeniedHttpException('Resource \'' . class_basename(static::model()) . '\' with given ID ' . $id . ' does not belong to ' .
                'resource \'' . class_basename(static::parentModel()) . '\' with given ID ' . $parentId . '; ');
        }

        $this->authorizeUserAction('update', $resource);

        // Validate the resource data with the updates
        $this->restfulService->validateResourceUpdate($resource, $request->input());
        
        // Patch model
        $this->restfulService->persistResource($resource->fill($request->input()));

        // Get updated resource
        $resource = $model::with($model->getAuthorizedWith())->where($model->getKeyName(), '=', $id)->first();

        if ($this->shouldTransform()) {
            $response = $this->response->item($resource, $this->getTransformer());
        } else {
            $response = $resource;
        }

        return $response;
    }

    /**
     * Deletes a child resource by ID
     *
     * @param string $parentId ID of the parent resource
     * @param string $id ID of the child resource
     * @return \Dingo\Api\Http\Response
     * @throws HttpException
     */
    public function delete($parentId, $id)
    {
        // Check parent exists
        $parentModel = static::parentModel();
        $parentResource = $parentModel::findOrFail($parentId);

        // Authorize ability to delete children model for parent
        $this->authorizeUserAction($this->parentAbilitiesRequired['delete'], $parentResource);

        $resource = static::model()::findOrFail($id);

        $this->authorizeUserAction('delete', $resource);

        // Check resource belongs to parent
        if ($resource->getAttribute(($parentResource->getKeyName())) != $parentResource->getKey()) {
            throw new AccessDeniedHttpException('Resource \'' . class_basename(static::model()) . '\' with given ID ' . $id . ' does not belong to ' .
                'resource \'' . class_basename(static::parentModel()) . '\' with given ID ' . $parentId . '; ');
        }

        $deletedCount = $resource->delete();

        if ($deletedCount < 1) {
            throw new NotFoundHttpException('Could not find a resource with that ID to delete');
        }

        return $this->response->noContent()->setStatusCode(204);
    }
}
