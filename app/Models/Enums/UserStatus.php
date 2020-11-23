<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class UserStatus extends Enum
{
    const Active   = 'active';
    const Inactive = 'inactive';
    const Blocked  = 'blocked';
}