<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Maps Spatie\QueryBuilderRequest public methods
 * to named parameters.
 */
final class SpatieQuery extends Enum
{
    const Include = "includes";
    const Append  = "appends";
    const Filter  = "filters";
    const Sort    = "sorts";
    const Field   = "fields";
}