<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Config;

/**
 * Maps Client Query configuration.
 */
final class ClientQuery extends Enum
{
    const ResetPassword = '/reset-password/{token}/{email}';
    const ActivateAccount = '/activate/{userId}/{token}';

    /**
     * Returns enum item as url
     */
    public static function url(ClientQuery $query, $parameters = [])
    {
        $url = url(Config::get('client.url') . $query->value);

        // replace url {} attributes from keys
        foreach($parameters as $key => $value) { 
            $url = str_ireplace('{'.$key.'}', $value, $url);
        }

        return $url;
    }
}