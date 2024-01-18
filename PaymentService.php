<?php

namespace App\Services\Payment;

use App\Services\Payment\Enums\PaymentGateway;
use App\Services\Payment\Gateways\Checkout\CheckOutPaymentService;
use App\Services\Payment\Interfaces\PaymentServiceInterface;

class PaymentService
{

    private function __construct()
    {
    }

    public static function getInstance($gateway = PaymentGateway::Checkout): ?PaymentServiceInterface
    {
        switch ($gateway) {
            case PaymentGateway::Checkout:
                return new CheckOutPaymentService();
                break;
            default:
                return new CheckOutPaymentService();
                break;
        }
    }
}
