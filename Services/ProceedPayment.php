<?php

namespace App\Services\Payment\Services;

use App\Services\Payment\Models\Payment;
use App\Services\Payment\Models\PaymentMethodTransaction;

class ProceedPayment
{
    private Payment $payment;
    function __construct(Payment $payment)
    {
        $payment->loadMissing(['currentProcessingPayment', 'currentProcessingPayment.currentPaymentMethodTransaction']);
        $this->payment = $payment;
    }

    function execute()
    {
        if (!$this->payment->isFinished() && $this->payment->currentProcessingPayment) {
            /**
             * @var PaymentMethodTransaction
             */
            return $this->payment->currentProcessingPayment->strategy()->proceed();
        }
    }
}
