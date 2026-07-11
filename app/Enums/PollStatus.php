<?php

namespace App\Enums;

enum PollStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
