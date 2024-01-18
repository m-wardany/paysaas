<?php

namespace App\Services\Payment\Enums;

enum PaymentMethodAction: string
{
    case ACTION_TYPE_PAY                    = 'Pay';
    case ACTION_TYPE_CAPTURE                = 'Capture';
    case ACTION_TYPE_RETURN                 = 'Return';
    case ACTION_TYPE_PRTIAL_RETURN          = 'Parial_Return';
}
