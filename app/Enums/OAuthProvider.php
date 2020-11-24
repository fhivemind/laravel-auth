<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Implemented OAuth Providers.
 * 
 * Values should name providers implemented by
 * https://socialiteproviders.com/
 */
final class OAuthProvider extends Enum
{
    const Github = "github";
    const Google  = "google";
}