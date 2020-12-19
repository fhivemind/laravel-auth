<?php
namespace App\Notifications;

use App\Enums\ClientQuery;
use Config;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;

class VerifyEmail extends BaseVerifyEmail
{
    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
        $rase = ClientQuery::url(ClientQuery::ActivateAccount(), [
            'token' => sha1($notifiable->getEmailForVerification()),
            'userId' => $notifiable->getKey(),
        ]);

        return $rase;
    }

}
