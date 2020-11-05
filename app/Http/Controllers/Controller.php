<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Specialtactics\L5Api\Http\Controllers\RestfulController as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
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
}
