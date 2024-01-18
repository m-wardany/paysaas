<?php

namespace App\Services\Payment\Strategies;

use App\Services\Payment\Models\PaymentMethod;
use App\Services\Payment\Enums\PaymentStatus as PaymentStatusEnum;
use App\Services\Payment\Enums\PaymentMethod as PaymentMethodTypeEnum;
use App\Services\Payment\Enums\PaymentMethodAction;
use App\Services\Payment\Enums\PaymentMethodSort as PaymentMethodSortEnum;
use Illuminate\Support\Facades\Crypt;

final class Card
{
    static function build($amount, $currencyId, $cardId, $isRefundable = false, $cvv = null): PaymentMethod
    {
        return  new PaymentMethod([
            'amount' => $amount,
            'currency_id' => $currencyId,
            'status' => PaymentStatusEnum::STATUS_INIT,
            'sort' => PaymentMethodSortEnum::PAYMENT_METHOD_CARD,
            'payment_option' => PaymentMethodTypeEnum::PAYMENT_METHOD_CARD,
            'is_refundable' => $isRefundable,
            'action' => PaymentMethodAction::ACTION_TYPE_PAY,
            'meta' => [
                'cvv' => self::encryptCvv((string)$cvv),
                'card_id' => $cardId
            ]
        ]);
    }

    static function encryptCvv($cvv): string|null
    {
        if (!empty($cvv)) {
            return Crypt::encryptString($cvv);
        }
        return null;
    }
}
