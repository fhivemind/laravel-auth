<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Services\RestfulService;
use Specialtactics\L5Api\Http\Controllers\RestfulController as RestfulController;

/**
 * 
 * Implements Restful Controller.
 * 
 * 
 * Following methods have been implemented for every controller.
 * Each method checks authorization policy before invoking any
 * other methods.
 * 
 *      > public function getAll()
 *      > public function get($id)
 *      > public function post(Request $request)
 *      > public function put(Request $request, $id)
 *      > public function patch($id, Request $request)
 *      > public function delete($id)
 * 
 * 
 * Function-to-Policy mappings shows which policy is invoked
 * for each method. 
 * 
 * MAPPINGS = [
 *      "getAll" => "viewAll"
 *      "get"    => "view"
 *      "post"   => "create"
 *      "put"    => "create,update"
 *      "patch"  => "update"
 *      "delete" => "delete"
 * ]
 * 
 * Complete list of Policy methods available at 
 *      App\Models\Policies\BasePolicy
 * 
 */
class Controller extends RestfulController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Specify the repository that you want to be associated with this controller.
     *
     * @var \App\Repositories\BaseRepository $repository
     */
    public static $repository = null;

    /**
     * Model Object associated with this controller. Created from static model field.
     *
     * @var \App\Models\BaseModel $model
     */
    private $modelCls = null;

    /**
     * @return \App\Models\BaseModel
     */
    protected function model() {
        return $this->modelCls;
    }

    /**
     * Repository Object associated with this controller. Created from static repository field.
     *
     * @var \App\Repositories\BaseRepository $repository
     */
    private $repositoryCls = null;

    /**
     * @return \App\Repositories\BaseRepository
     */
    protected function repository() {
        return $this->repositoryCls;
    }

    /**
     * Controller constructor.
     *
     * @param RestfulService $restfulService
     */
    public function __construct(RestfulService $restfulService)
    {
        parent::__construct($restfulService);

        // Initialize class instances.
        if (!is_null(static::$model))
            $this->modelCls = new static::$model;
            
        if (!is_null(static::$repository))
            $this->repositoryCls = new static::$repository;
    }
}
