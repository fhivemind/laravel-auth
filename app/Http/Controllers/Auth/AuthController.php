<?php

namespace App\Http\Controllers\Auth;

use App\Models\Enums\UserStatus as EnumsUserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Features\JWTAuthenticationTrait;
use App\Http\Controllers\Features\OAuthAuthenticationTrait;
use App\Models\AuthenticatedUser;
use App\Models\UserStatus;
use Dingo\Api\Exception\ResourceException;
use Dingo\Api\Http\Response;
use Hash;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    use JWTAuthenticationTrait, OAuthAuthenticationTrait;

    /**
     * Creates a new user from request.
     *
     * @param Request $request
     * @return Response
     * 
     * @throws ResourceException
     * @throws UnprocessableEntityHttpException
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|min:3|max:255',
            'email' => 'required|string|email|max:255|unique:user',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // validate request
        if ($validator->fails()) {
            throw new ResourceException("Validation failed.", $validator->errors());
        }

        // create user
        $user = new User($request->only(['username', 'email']));
        $user->password = Hash::make($request->password);
        $user->id_status = UserStatus::getStatusId(EnumsUserStatus::Inactive());

        // validate and save
        $user = $this->restfulService->persistResource($user);

        // do login
        $user = AuthenticatedUser::find($user->id);
        $token = auth()->login($user);

        // notify
        event(new Registered($user));

        // send token
        return $this->respondWithToken($token);
    }
}
