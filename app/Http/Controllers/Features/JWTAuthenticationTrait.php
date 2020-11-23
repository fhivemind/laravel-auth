<?php

namespace App\Http\Controllers\Features;

use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use App\Exceptions\UnauthorizedHttpException;

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
     * Get the token array structure.
     *
     * @param string $token
     * @return Response
     */
    protected function respondWithToken($token)
    {
        $tokenReponse = new \Stdclass;

        $tokenReponse->jwt = $token;
        $tokenReponse->token_type = 'bearer';
        $tokenReponse->expires_in = auth()->factory()->getTTL();

        return $this->response->item($tokenReponse, $this->getTransformer())->setStatusCode(200);
    }
}
