<?php

namespace App\Services\Payment\Enums;

enum PaymentAction: int
{
    case ACTION_CREATE_ORDER            = 1;
    case ACTION_ADD_CARD                = 2;
}
