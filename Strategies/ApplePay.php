<?php

namespace App\Services\Payment\Strategies;

use App\Services\Payment\Models\PaymentMethod;
use App\Services\Payment\Enums\PaymentStatus as PaymentStatusEnum;
use App\Services\Payment\Enums\PaymentMethod as PaymentMethodTypeEnum;
use App\Services\Payment\Enums\PaymentMethodAction;
use App\Services\Payment\Enums\PaymentMethodSort as PaymentMethodSortEnum;

final class ApplePay
{
    static function build($amount, $currencyId, $token, $isRefundable = false): PaymentMethod
    {
        return  new PaymentMethod([
            'amount' => $amount,
            'currency_id' => $currencyId,
            'status' => PaymentStatusEnum::STATUS_INIT,
            'sort' => PaymentMethodSortEnum::PAYMENT_METHOD_APPLE_PAY,
            'payment_option' => PaymentMethodTypeEnum::PAYMENT_METHOD_TOKEN,
            'is_refundable' => $isRefundable,
            'action' => PaymentMethodAction::ACTION_TYPE_PAY,
            'meta' => [
                'token' => $token,
            ]
        ]);
    }
}
