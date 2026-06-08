<?php

declare(strict_types=1);

namespace App\Enum;

enum Rating: int
{
    case R1 = 1;
    case R2 = 2;
    case R3 = 3;
    case R4 = 4;
    case R5 = 5;
}
