<?php

namespace App\Services\Payment\Enums;

enum PaymentStatus: int
{
    case STATUS_INIT                    = 1;
    case STATUS_PENDING                 = 2;
    case STATUS_SUCCEED                 = 3;
    case STATUS_REVERTED                = 4;
    case STATUS_FAILED_PROCEED          = 5;
    case STATUS_FAILED_REVERT           = 6;
}
