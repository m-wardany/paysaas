<?php

namespace App\Services\Payment\Strategies;

use App\Models\Currency;
use App\Services\Payment\Models\PaymentMethod;
use App\Services\Payment\Models\PaymentMethodTransaction;
use App\Services\Payment\PaymentResponse;
use App\Services\Payment\PaymentService;

abstract class PaymentStrategy
{

    protected PaymentMethod $paymentMethod;

    protected PaymentMethodTransaction|null $currentPaymentMethodTransaction;

    protected $service;

    function __construct(PaymentMethod $paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;
        $this->currentPaymentMethodTransaction = $paymentMethod->currentPaymentMethodTransaction;
        $this->service = PaymentService::getInstance(optional($paymentMethod->currentPaymentMethodTransaction)->vendor ?? null);
    }

    function getPaymentMethod(): PaymentMethod
    {
        return $this->paymentMethod;
    }

    function getAmount(): float
    {
        return $this->paymentMethod->amount;
    }

    function getCurrency(): Currency
    {
        return $this->paymentMethod->currency;
    }

    function reference(): int
    {
        return $this->paymentMethod->id;
    }

    function createPaymentTransaction(PaymentResponse $response, $action)
    {
        return $this->paymentMethod->paymentMethodTransactions()->create([
            'action' => $action,
            'approved' => $response->getIsApproved(),
            'reference' => $response->getReference(),
            'status' => $response->getStatus(),
            'vendor_status' => $response->getVendorStatus(),
            'vendor' => $response->getVendor(),
            'request' => $response->getRequest(),
            'response' => $response,
            'processed_at' => $response->getProcessedOn(),
        ]);
    }


    abstract static function build($amount, $currencyId, $token, $isRefundable = true): PaymentMethod;

    abstract function proceed(): PaymentMethodTransaction;

    abstract function check(): PaymentMethodTransaction;
}
