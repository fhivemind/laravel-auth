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
Route::get('/', function () 
{
    echo 'Jourfixer API';
});

/** @var \Dingo\Api\Routing\Router $api */
$api = app('Dingo\Api\Routing\Router');
$api->version('v1', ['middleware' => ['api']], function (Router $api)
{
    /*
     * Authentication
     */
    $api->group(['prefix' => 'auth'], function (Router $api)
    {
        // Auth
        $api->get ('/login', 'App\Http\Controllers\Auth\AuthController@token')->name('login');
        $api->post('/register', 'App\Http\Controllers\Auth\AuthController@register')->name('register');

        // OAuth
        $api->get('/oauth/{provider}', 'App\Http\Controllers\Auth\AuthController@redirectToProvider')->name('oauth.redirect');
        $api->post('/oauth/callback/{provider}', 'App\Http\Controllers\Auth\AuthController@handleProviderCallback')->name('oauth.callback');

        // Password reset
        $api->post('/password/email', 'App\Http\Controllers\Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
        $api->post('/password/reset', 'App\Http\Controllers\Auth\ResetPasswordController@reset')->name('password.reset');
    });

    /*
     * Authenticated routes
     */
    $api->group(['middleware' => ['api.auth']], function (Router $api)
    {
        /*
         * Authentication
         */
        $api->group(['prefix' => 'auth'], function (Router $api) 
        {
            // User
            $api->get('/me', 'App\Http\Controllers\Auth\AuthController@getUser')->name('me');
            $api->delete('/logout', 'App\Http\Controllers\Auth\AuthController@logout')->name('logout');

            // Token
            $api->get('/token/refresh', 'App\Http\Controllers\Auth\AuthController@refresh')->name('token.refresh');

            // Email verification
            $api->get('/email/verify/{id}/{hash}', 'App\Http\Controllers\Auth\VerificationController@verify')->name('verification.verify');
            $api->post('/email/resend', 'App\Http\Controllers\Auth\VerificationController@resend')->name('verification.resend');
        });

        /*
         * Users
         */
        $api->group(['prefix' => 'user'], function (Router $api) 
        {
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
        $api->group(['prefix' => 'user_status'], function (Router $api) 
        {
            $api->get('/', 'App\Http\Controllers\UserStatusController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\UserStatusController@get');
        });

        /*
         * Roles
         */
        $api->group(['prefix' => 'role'], function (Router $api) 
        {
            $api->get('/', 'App\Http\Controllers\RoleController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\RoleController@get');
        });

        /*
         * User Roles
         */
        $api->group(['prefix' => 'user_role'], function (Router $api) 
        {
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
        $api->group(['prefix' => 'user_log'], function (Router $api) 
        {
            $api->get('/', 'App\Http\Controllers\UserLogsController@getAll');
            $api->get('/{uuid}', 'App\Http\Controllers\UserLogsController@get');
        });

        /*
         * User Referrals
         */
        $api->group(['prefix' => 'referral'], function (Router $api) 
        {
            $api->get('/', 'App\Http\Controllers\ReferralController@getAll');
            $api->get('/{id}', 'App\Http\Controllers\ReferralController@get');
        });
    });
});
