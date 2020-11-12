<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;

/**
 * 
 * Implements Restful Controller.
 * 
 * 
 * Following methods have been implemented for every controller.
 * Each method checks authorization policy before invoking anything.
 * Every method that accepts request supports advanced querying.
 * 
 * @method public static function repository() - returns repository name connected to this controller
 * @method public function getAll(Request $request) - GET /
 * @method public function get($id, Request $request) - GET /{id}
 * @method public function post(Request $request) - POST /
 * @method public function put(Request $request, $id) - PUT /{id}
 * @method public function patch($id, Request $request) - PATCH /{id}
 * @method public function delete($id) - DELETE /{id}
 * 
 * 
 * @var BaseTransformer public static $transformer - transformer to use for controller resources 
 * in case repository is unspecified
 * 
 * @var array
 *      > "getAll" => "viewAll",          \
 *      > "get"    => "view",             \
 *      > "post"   => "create",           \
 *      > "put"    => "create,update",    \
 *      > "patch"  => "update",           \
 *      > "delete" => "delete"
 * 
 * Function-to-Policy mappings shows which policy is invoked
 * for each method. 
 * 
 * 
 */
abstract class Controller extends RestfulController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
