<?php

namespace App\Services\Payment\Services;

use App\Services\Payment\Enums\PaymentAction;
use App\Services\Payment\Models\Payment;

class HandleSuccessfulPaymentService
{
    private Payment $payment;
    
    function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    function execute()
    {
        switch ($this->payment->action) {
            case PaymentAction::ACTION_ADD_CARD:
                
                break;
            
            default:
                # code...
                break;
        }
    }
}
