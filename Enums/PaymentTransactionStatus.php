<?php

namespace App\Services\Payment\Enums;

enum PaymentTransactionStatus: int
{
    case STATUS_PENDING                 = 1;
    case STATUS_FINISHED                = 2;
}
