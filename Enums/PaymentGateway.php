<?php

namespace App\Services\Payment\Enums;

Enum PaymentGateway: string
{
    case Checkout = 'Checkout';
    case Previous_Checkout = 'Previous_Checkout';
}
