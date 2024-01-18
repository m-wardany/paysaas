<?php

namespace App\Services\Payment\Services;

use App\Services\Payment\Models\Payment;
use App\Services\Payment\Models\PaymentMethod;
use App\Services\Payment\Models\PaymentMethodTransaction;

class RevertPayment
{
    private Payment $payment;
    function __construct(Payment $payment)
    {
        $payment->loadMissing(['succeedPaymentMethods']);
        $this->payment = $payment;
    }

    function execute()
    {
        /**
         * @var PaymentMethod
         */
        $paymentMethod = $this->payment->succeedPaymentMethods->first();
        if ($paymentMethod) {
            return $paymentMethod->strategy()->revert();
        }
    }
}
