<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;
    
    protected function sendResetLinkResponse(Request $request, $response)
    {
        $response = ['message' => "Password reset email sent"];
        return response($response, 200);
    }

    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        $response = ['message' => "Unable to send email to provided address"];
        return response($response, 500);
    }
}
