<?php

namespace App\Services\Payment\Strategies;

use App\Services\Payment\Models\PaymentMethod;
use App\Services\Payment\Models\PaymentMethodTransaction;
use App\Services\Payment\Enums\PaymentStatus as PaymentStatusEnum;
use App\Services\Payment\Enums\PaymentMethod as PaymentMethodTypeEnum;
use App\Services\Payment\Enums\PaymentMethodAction;
use App\Services\Payment\Enums\PaymentMethodSort as PaymentMethodSortEnum;
use App\Services\Payment\Enums\PaymentTransactionType;
use Illuminate\Support\Facades\Crypt;

final class Token extends PaymentStrategy
{

    static function build($amount, $currencyId, $token, $isRefundable = false): PaymentMethod
    {
        return  new PaymentMethod([
            'amount' => $amount,
            'currency_id' => $currencyId,
            'status' => PaymentStatusEnum::STATUS_INIT,
            'sort' => PaymentMethodSortEnum::PAYMENT_METHOD_TOKEN,
            'payment_option' => PaymentMethodTypeEnum::PAYMENT_METHOD_TOKEN,
            'is_refundable' => $isRefundable,
            'action' => PaymentMethodAction::ACTION_TYPE_PAY,
            'meta' => [
                'token' => self::encryptToken($token),
            ]
        ]);
    }

    static function encryptToken($token): string|null
    {
        if (!empty($token)) {
            return Crypt::encryptString($token);
        }
        return null;
    }

    function token(): string|null
    {
        $token = data_get($this->paymentMethod->meta, 'token');
        if ($token) {
            return Crypt::decryptString($token);
        }
        return null;
    }

    function proceed() : PaymentMethodTransaction
    {
        $createNewTransaction = true;
        if ($this->currentPaymentMethodTransaction) {
            $response = $this->service->getPaymentDetails($this->currentPaymentMethodTransaction->reference);
            $createNewTransaction = $this->currentPaymentMethodTransaction->canOverride($response->getProcessedOn());
        } else {
            $response = $this->service->payWithToken($this);
        }

        if ($createNewTransaction) {
            $this->currentPaymentMethodTransaction = $this->createPaymentTransaction($response, PaymentTransactionType::ACTION_TYPE_PURCHASE);
        }
        return $this->currentPaymentMethodTransaction;
    }

    function check(): PaymentMethodTransaction
    {
        $createNewTransaction = true;
        if ($this->currentPaymentMethodTransaction) {
            $response = $this->service->getPaymentDetails($this->currentPaymentMethodTransaction->reference);
            $createNewTransaction = $this->currentPaymentMethodTransaction->canOverride($response->getProcessedOn());
        } 

        if ($createNewTransaction) {
            $this->currentPaymentMethodTransaction = $this->paymentMethod->paymentMethodTransactions()->create([
                'action' => PaymentTransactionType::ACTION_TYPE_PURCHASE,
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
        return $this->currentPaymentMethodTransaction;
    }

    function revert()
    {
        
    }

    function processingAction(): PaymentTransactionType
    {
        return PaymentTransactionType::ACTION_TYPE_CAPTURE;
    }

    function revertingAction(): PaymentTransactionType
    {
        return PaymentTransactionType::ACTION_TYPE_REFUND;
    }
}
