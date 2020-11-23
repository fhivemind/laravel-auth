<?php

use Dingo\Api\Routing\Router;

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
        $api->get('/login', 'App\Http\Controllers\Auth\AuthController@token');
        $api->post('/register', 'App\Http\Controllers\Auth\AuthController@register');

        // TODO: Implement endpoints
        // $api->post('/reset/{mail}', 'App\Http\Controllers\Auth\AuthController@reset');
        // $api->post('/verify/{verification_code}', 'App\Http\Controllers\Auth\AuthController@verify');
    });

    /*
     * Authenticated routes
     */
    $api->group(['middleware' => ['api.auth']], function (Router $api) {
        /*
         * Authentication
         */
        $api->group(['prefix' => 'auth'], function (Router $api) {
            $api->group(['prefix' => 'token'], function (Router $api) {
                $api->get('/refresh', 'App\Http\Controllers\Auth\AuthController@refresh');
            });

            $api->delete('/logout', 'App\Http\Controllers\Auth\AuthController@logout');
            $api->get('/me', 'App\Http\Controllers\Auth\AuthController@getUser');
        });

        /*
         * Users
         */
        $api->group(['prefix' => 'user'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\UserController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\UserController@get');
            $api->post('/', 'App\Http\Controllers\UserController@post');
            $api->put('/{id}', 'App\Http\Controllers\UserController@put');
            $api->patch('/{id}', 'App\Http\Controllers\UserController@patch');
            $api->delete('/{id}', 'App\Http\Controllers\UserController@delete');
        });

        /*
         * Status
         */
        $api->group(['prefix' => 'user_status'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\UserStatusController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\UserStatusController@get');
        });

        /*
         * Roles
         */
        $api->group(['prefix' => 'role'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\RoleController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\RoleController@get');
        });

        /*
         * User Roles
         */
        $api->group(['prefix' => 'user_role'], function (Router $api) {
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
        $api->group(['prefix' => 'user_log'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\UserLogsController@getAll');
            $api->get('/{uuid}', 'App\Http\Controllers\UserLogsController@get');
        });

        /*
         * User Referrals
         */
        $api->group(['prefix' => 'referral'], function (Router $api) {
            $api->get('/', 'App\Http\Controllers\ReferralController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\ReferralController@get');
        });
    });
});
