<?php

namespace App\Services\Payment\Enums;

enum PaymentTransactionType: string
{
    case ACTION_TYPE_AUTHORIZATION          = 'Authorization';
    case ACTION_TYPE_PURCHASE               = 'Purchase';
    case ACTION_TYPE_CAPTURE                = 'Capture';
    case ACTION_TYPE_REFUND                 = 'Refund';
    case ACTION_TYPE_PRTIAL_REFUND          = 'Parial_Refund';
    case ACTION_TYPE_VOID                   = 'Void';
    case ACTION_TYPE_DEPOSIT                = 'Deposit';
    case ACTION_TYPE_WITHDRAW               = 'Withdraw';
}
