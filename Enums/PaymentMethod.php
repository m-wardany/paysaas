<?php

namespace App\Services\Payment\Enums;

enum PaymentMethod: int
{
    case PAYMENT_NOT_REQUIRED           = 0;
    case PAYMENT_METHOD_APPLE_PAY       = 1;
    case PAYMENT_METHOD_CARD            = 3;
    case PAYMENT_METHOD_WALLET          = 4;
    case PAYMENT_METHOD_TOKEN           = 5;
}
