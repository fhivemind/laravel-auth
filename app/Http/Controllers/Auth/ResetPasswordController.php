<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected function sendResetResponse(Request $request, $response)
    {
        $response = ['message' => "Password reset successful"];
        return response($response, 200);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        $response = "Token invalid";
        return response($response, 401);
    }

    /**
     * Reset the given user's password.
     *
     * @param  AuthenticatedUser  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        // Update password
        $this->setUserPassword($user, $password);
        $user->save();

        // Trigger event
        event(new PasswordReset($user));

        // Login
        $token = auth()->login($user);
    }
}
