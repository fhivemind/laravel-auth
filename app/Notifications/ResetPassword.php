<?php

namespace App\Notifications;

use App\Enums\ClientQuery;
use Config;
use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;

class ResetPassword extends BaseResetPassword
{
    public static $createUrlCallback = [self::class, 'createActionUrl'];

    public static function createActionUrl($notifiable, $token)
    {
        return ClientQuery::url(ClientQuery::ResetPassword(), [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset()
        ]);
    }
}