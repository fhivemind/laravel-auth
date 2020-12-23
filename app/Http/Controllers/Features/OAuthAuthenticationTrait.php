<?php

namespace App\Http\Controllers\Features;

use App\Enums\OAuthProvider;
use Illuminate\Http\Request;
use Dingo\Api\Http\Response;
use App\Models\AuthenticatedUser;
use App\Models\Enums\UserStatus as EnumsUserStatus;
use App\Models\User;
use App\Models\UserStatus;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Auth\Events\Registered;
use Laravel\Socialite\Facades\Socialite;

trait OAuthAuthenticationTrait
{
    /**
     * Redirect the user to the OAuth provider authentication page.
     * 
     * Example: 
     * * Github: https://github.com/login/oauth/authorize?scope=user:email&client_id=CLIENT_ID
     * * Google: https://accounts.google.com/o/oauth2/v2/auth?redirect_uri=REDIRECT_URI&client_id=CLIENT_ID
     *
     * @var string $provider
     * @return Response
     */
    public function redirectToProvider(string $provider, Request $request) {
        if (! OAuthProvider::hasValue($provider)) {
            throw new ResourceException("Invalid OAuth provider. Supported providers [". join(', ', OAuthProvider::getValues()) ."]");
        }

        $redirect = Socialite::driver($provider)->stateless()->redirect();
        return $redirect->getTargetUrl();
    }

    /**
     * Returns a map of all allowed OAuth providers.
     * 
     * @return Response
     */
    public function fetchProviders(Request $request)
    {
        $result = array();
        foreach (OAuthProvider::getValues() as $provider) {
            $result[$provider] = Socialite::driver($provider)
                                                ->stateless()
                                                ->redirect()
                                                ->getTargetUrl();
        }

        return $this->response->array($result, $this->getTransformer());
    }


    /**
     * Obtain the user information from OAuth provider.
     *
     * @var string $provider
     * @return JsonResponse
     */
    public function handleProviderCallback(string $provider, Request $request) {
        if (! OAuthProvider::hasValue($provider)) {
            throw new ResourceException("Invalid OAuth provider. Supported providers [". join(', ', OAuthProvider::getValues()) ."]");
        }

        // Handle provider authentication for user
        $providerUser = Socialite::driver($provider)->stateless()->user();

        // Find authenticated user
        $user = AuthenticatedUser::query()->firstOrNew(['email' => $providerUser->getEmail()]);

        // If not registered, create user account
        if (! $user->exists) 
        {
            // update data
            $user->username = $providerUser->getName();
            $user->id_status = UserStatus::getStatusId(EnumsUserStatus::Inactive());

            // validate and save
            $user = $this->restfulService->persistResource($user);

            // notify
            event(new Registered($user));
        }

        // do login
        $token = auth()->login($user);

        // send token
        return $this->respondWithToken($token);
    }
}
