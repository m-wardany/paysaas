<?php

namespace App\Services\Payment\Strategies;

use App\Services\Payment\Models\PaymentMethod;
use App\Services\Payment\Enums\PaymentStatus as PaymentStatusEnum;
use App\Services\Payment\Enums\PaymentMethod as PaymentMethodTypeEnum;
use App\Services\Payment\Enums\PaymentMethodAction;
use App\Services\Payment\Enums\PaymentMethodSort as PaymentMethodSortEnum;

final class Wallet
{
    static function build($amount, $currencyId, $is_refundable = false): PaymentMethod
    {
        return  new PaymentMethod([
            'amount' => $amount,
            'currency_id' => $currencyId,
            'is_refundable' => $is_refundable,
            'status' => PaymentStatusEnum::STATUS_INIT,
            'sort' => PaymentMethodSortEnum::PAYMENT_METHOD_WALLET,
            'payment_option' => PaymentMethodTypeEnum::PAYMENT_METHOD_WALLET,
            'action' => PaymentMethodAction::ACTION_TYPE_PAY,
        ]);
    }
}
