<?php

namespace App\Http\Controllers\Features;

use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use App\Exceptions\UnauthorizedHttpException;
use App\Models\AuthenticatedUser;
use App\Models\User;
use Dingo\Api\Exception\ResourceException;

trait JWTAuthenticationTrait
{
    /**
     * Get a JWT via given credentials.
     *
     * @param Request $request
     * @return Response
     */
    public function token(Request $request)
    {
        $authHeader = $request->header('Authorization');

        // Get for Auth Basic
        if (strtolower(substr($authHeader, 0, 5)) !== 'basic') {
            throw new UnauthorizedHttpException('Invalid authorization header, should be type basic');
        }

        // Get credentials
        $credentials = base64_decode(trim(substr($authHeader, 5)));

        [$email, $password] = explode(':', $credentials, 2);

        // validate request
        // Find authenticated user
        if (! AuthenticatedUser::query()->where('email', $email)->first() ) {
            throw new ResourceException("Invalid login data.", ["email" => "User with provided email does not exist."]);;
        }

        // Do auth
        if (! $token = auth()->attempt(['email' => $email, 'password' => $password])) {
            throw new UnauthorizedHttpException('Unauthorized login');
        }

        return $this->respondWithToken($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return Response
     */
    public function logout()
    {
        auth()->logout();

        return $this->response->noContent();
    }

    /**
     * Refreshes a jwt (ie. extends it's TTL)
     *
     * @return Response
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return Response
     */
    public function getUser()
    {
        return $this->api->raw()->get('user/' . $this->auth->user()->getKey());
    }

    /**
     * Respond with authorized data.
     *
     * @param string $token
     * @return Response
     */
    protected function respondWithToken($token)
    {
        $tokenReponse = new \Stdclass;

        $tokenReponse->user = auth()->user();
        $tokenReponse->token = new \Stdclass;
        $tokenReponse->token->jwt = $token;
        $tokenReponse->token->token_type = 'bearer';
        $tokenReponse->token->expires_in = auth()->factory()->getTTL();

        return $this->response->item($tokenReponse, $this->getTransformer())->setStatusCode(200);
    }
}
