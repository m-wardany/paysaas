<?php

namespace App\Services\Payment\Builders;

use App\Services\Payment\Models\Payment as PaymentModel;
use App\Services\Payment\Enums\PaymentStatus as PaymentStatusEnum;
use App\Services\Payment\Strategies\Wallet as WalletStrategy;
use App\Services\Payment\Strategies\Card as CardStrategy;
use App\Services\Payment\Strategies\Token as TokenStrategy;
use App\Services\Payment\Strategies\ApplePay as ApplePayStrategy;
use Illuminate\Support\Facades\DB;

class Payment
{
    private PaymentModel $payment;
    private $wallet = [];
    private $card = [];
    private $applePay = [];
    private $token = [];
    private $payable;
    private $payer;

    private function __construct(PaymentModel $payment)
    {
        $this->payment = $payment;
    }

    public static function init(): self
    {
        $obj = new self(
            new PaymentModel([
                'status' => PaymentStatusEnum::STATUS_INIT,
                'is_refundable' => false,
            ])
        );
        return $obj;
    }

    function setPayable($payable): self
    {
        $this->payable = $payable;
        return $this;
    }

    function setPayer($payer): self
    {
        $this->payer = $payer;
        return $this;
    }

    function setReference($reference): self
    {
        $this->payment->reference()->associate($reference);
        return $this;
    }

    /**
     * 
     * @param PaymentAction $action
     * @return self
     */
    function setAction($action): self
    {
        $this->payment->action = $action;
        return $this;
    }

    function setAmount($amount, $currency_id): self
    {
        $this->payment->amount = $amount;
        $this->payment->currency_id = $currency_id;
        return $this;
    }

    function addToMeta($key, $value)
    {
        $meta = $this->payment->meta;
        $meta[$key] = $value;
        $this->payment->meta = $meta;
    }

    function addWallet($amount): self
    {
        $this->wallet[] = WalletStrategy::build(
            amount: $amount,
            currencyId: $this->payment->currency_id,
        );
        return $this;
    }

    function addToken($amount, $token): self
    {
        $this->token[] = TokenStrategy::build(
            amount: $amount,
            currencyId: $this->payment->currency_id,
            token: $token
        );
        return $this;
    }

    function addCard($amount, $cardId, $cvv = null): self
    {
        $this->card[] = CardStrategy::build(
            amount: $amount,
            currencyId: $this->payment->currency_id,
            cardId: $cardId,
            cvv: $cvv
        );
        return $this;
    }

    function addApplePay($amount, $token): self
    {
        $this->applePay[] = ApplePayStrategy::build(
            amount: $amount,
            currencyId: $this->payment->currency_id,
            token: $token
        );
        return $this;
    }

    /**
     * @return PaymentModel|null
     */
    function build(): PaymentModel|null
    {
        DB::transaction(function () {
            if ($this->payable) {
                $this->payment->payable()->associate($this->payable);
            }

            if ($this->payer) {
                $this->payment->payer()->associate($this->payer);
            }

            $this->payment->save();

            foreach ($this->getPaymentMethods() as $paymentMethod) {
                $this->payment->paymentMethods()->save($paymentMethod);
            }
        });
        return $this->payment ?? null;
    }

    private function getPaymentMethods(): array
    {
        return array_merge($this->card, $this->wallet, $this->token, $this->applePay);
    }
}
