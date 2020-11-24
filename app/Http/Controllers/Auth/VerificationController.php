<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuthenticatedUser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function verify(string $id, string $hash, Request $request)
    {
        // Find authenticated user
        $user = AuthenticatedUser::find($request->route('id'));
        if (! $user) {
            throw new AuthorizationException;
        }

        // Check if valid hash
        if (! hash_equals((string) $request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return response(['message' => "Already verified"], 204);
        }

        // Verify user
        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        // Send response
        return response(['message' => "Successfully verified"], 200);
    }

    /**
     * Resend the email verification notification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request)
    {
        // Find authenticated user
        $user = AuthenticatedUser::query()->where('email', $request->get('email'))->first();
        if (! $user) {
            throw new AuthorizationException;
        }
        
        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return $this->response(['message' => "Already verified"], 204);
        }

        // Send verification email
        $user->sendEmailVerificationNotification();

        // Send response
        return response(['message' => "Verification email sent"], 202);
    }
}
