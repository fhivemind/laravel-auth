<?php

use Dingo\Api\Routing\Router;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
 * Welcome route
 * TODO: Replace this with the complete list of APIs
 */
Route::get('/', function () {
    echo 'Jourfixer API';
});

/** @var \Dingo\Api\Routing\Router $api */
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', ['middleware' => ['api']], function (Router $api) {
    /*
     * Authentication
     */
    $api->group(['prefix' => 'auth'], function (Router $api) {
        $api->group(['prefix' => 'jwt'], function (Router $api) {
            $api->get('/token', 'App\Http\Controllers\Auth\AuthController@token');
        });
    });

    /*
     * Authenticated routes
     */
    $api->group(['middleware' => ['api.auth']], function (Router $api) {
        /*
         * Authentication
         */
        $api->group(['prefix' => 'auth'], function (Router $api) {
            $api->group(['prefix' => 'jwt'], function (Router $api) {
                $api->get('/refresh', 'App\Http\Controllers\Auth\AuthController@refresh');
                $api->delete('/logout', 'App\Http\Controllers\Auth\AuthController@logout');
            });

            $api->get('/me', 'App\Http\Controllers\Auth\AuthController@getUser');
        });

        /*
         * Roles
         */
        $api->group(['prefix' => 'roles'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\RoleController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\RoleController@get');
        });

        /*
         * Users
         */
        $api->group(['prefix' => 'users'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\UserController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\UserController@get');
            $api->post('/', 'App\Http\Controllers\UserController@post');
            $api->put('/{id}', 'App\Http\Controllers\UserController@put');
            $api->patch('/{id}', 'App\Http\Controllers\UserController@patch');
            $api->delete('/{id}', 'App\Http\Controllers\UserController@delete');
        });

        /*
         * User Roles
         */
        $api->group(['prefix' => 'user_roles'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\UserRoleController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\UserRoleController@get');
            $api->post('/', 'App\Http\Controllers\UserRoleController@post');
            $api->put('/{id}', 'App\Http\Controllers\UserRoleController@put');
            $api->patch('/{id}', 'App\Http\Controllers\UserRoleController@patch');
            $api->delete('/{id}', 'App\Http\Controllers\UserRoleController@delete');
        });

        /*
         * User Logs
         */
        $api->group(['prefix' => 'user_logs'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\UserLogsController@getAll');
            $api->get('/{uuid}', 'App\Http\Controllers\UserLogsController@get');
        });

        /*
         * User Referrals
         */
        $api->group(['prefix' => 'referrals'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\ReferralController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\ReferralController@get');
        });
    });
});
