<?php

namespace App\Services\Payment\Enums;

enum PaymentMethodStatus: int
{
    case STATUS_INIT                    = 0;
    case STATUS_PENDING                 = 1;
    case STATUS_SUCCEED                 = 2;
    case STATUS_FAILED                  = 3;
}
