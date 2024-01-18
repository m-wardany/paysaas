<?php

namespace App\Services\Payment\Enums;

enum PaymentMethodSort: int
{
    case PAYMENT_METHOD_APPLE_PAY       = 1;
    case PAYMENT_METHOD_CARD            = 2;
    case PAYMENT_METHOD_TOKEN           = 3;
    case PAYMENT_METHOD_WALLET          = 4;
}
