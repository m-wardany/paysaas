<?php

namespace App\Services\Payment\Services;

use App\Services\Payment\Models\Payment;
use App\Services\Payment\Models\PaymentMethodTransaction;
use App\Services\Payment\PaymentResponse;
use App\Services\Payment\PaymentService;

class CheckPayment
{
    private $service;
    private $paymentId;
    private PaymentMethodTransaction|null $paymentTransaction;
    function __construct($paymentId)
    {
        $this->paymentId = $paymentId;
        $this->paymentTransaction = PaymentMethodTransaction::wherePaymentId($paymentId)->with(['paymentMethod', 'paymentMethod.payment'])->first();
        if ($this->paymentTransaction) {
            $this->service = PaymentService::getInstance($this->paymentTransaction->vendor);
        } else {
            $this->service = PaymentService::getInstance();
        }
    }

    function execute()
    {
        /**
         * @var PaymentResponse
         */
        $response = $this->service->getPaymentDetails($this->paymentId);
        return $response;
        // $this->paymentTransaction->canOverride($response->getProcessedOn());
        // dd($response, $response->getProcessedOn(), optional($this->paymentTransaction)->processed_at, $this->paymentTransaction->canOverride($response->getProcessedOn()));
    }
}
