<?php

namespace App\Services\Payment\Models;

use App\Models\Currency;
use App\Services\Payment\Enums\PaymentMethodStatus as EnumsPaymentMethodStatus;
use App\Services\Payment\Enums\PaymentMethod as EnumsPaymentMethod;
use App\Services\Payment\Enums\PaymentMethodAction;
use App\Services\Payment\Strategies\ApplePay as ApplePayStrategy;
use App\Services\Payment\Strategies\Card as CardStrategy;
use App\Services\Payment\Strategies\Token as TokenStrategy;
use App\Services\Payment\Strategies\Wallet as WalletStrategy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasFactory;

    // public $last_response;

    protected $fillable = [
        'action',
        'status',
        'sort',
        'payment_id',
        'payment_option',
        'amount',
        'refunded_amount',
        'currency_id',
        'processed_at',
        'is_refundable',
        'meta',
        'last_response',
    ];

    protected $casts = [
        'meta' => 'array',
        'status' => EnumsPaymentMethodStatus::class,
        'action' => PaymentMethodAction::class,
        'payment_option' => EnumsPaymentMethod::class,
        'last_response' => \App\Services\Payment\Casts\Response::class,

    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function paymentMethodTransactions()
    {
        return $this->hasMany(PaymentMethodTransaction::class);
    }

    public function currentPaymentMethodTransaction()
    {
        return $this->hasOne(PaymentMethodTransaction::class)->latestOfMany();
    }

    public function succeededPaymentMethodTransaction()
    {
        return $this->hasOne(PaymentMethodTransaction::class)->latestOfMany();
    }

    function scopeIsToken($query)
    {
        return $query->where('payment_option', EnumsPaymentMethod::PAYMENT_METHOD_TOKEN);
    }

    function scopeIsPending($query)
    {
        return $query->where('status', EnumsPaymentMethodStatus::STATUS_PENDING);
    }

    function scopeIsProceedable($query)
    {
        return $query->whereIn('status', [
            EnumsPaymentMethodStatus::STATUS_INIT,
            EnumsPaymentMethodStatus::STATUS_PENDING,
        ]);
    }

    function scopeIsProcessed($query)
    {
        return $query->where('status', EnumsPaymentMethodStatus::STATUS_SUCCEED)->whereIn('action', [PaymentMethodAction::ACTION_TYPE_PAY, PaymentMethodAction::ACTION_TYPE_CAPTURE]);
    }

    function scopeIsFailed($query)
    {
        return $query->where('status', EnumsPaymentMethodStatus::STATUS_FAILED);
    }

    function isPending(): bool
    {
        return $this->status == EnumsPaymentMethodStatus::STATUS_PENDING;
    }

    function isSucceed(): bool
    {
        return $this->status == EnumsPaymentMethodStatus::STATUS_SUCCEED;
    }

    function isFailed(): bool
    {
        return $this->status == EnumsPaymentMethodStatus::STATUS_FAILED;
    }


    static function boot()
    {
        parent::boot();
        static::updated(function (self $paymentMethod) {
            $paymentMethod->payment->updateStatusIfNeeded($paymentMethod);
        });
    }

    function strategy()
    {
        switch ($this->payment_option) {
            case EnumsPaymentMethod::PAYMENT_METHOD_TOKEN:
                return new TokenStrategy($this);
                break;
            case EnumsPaymentMethod::PAYMENT_METHOD_WALLET:
                return new WalletStrategy($this);
                break;
            case EnumsPaymentMethod::PAYMENT_METHOD_CARD:
                return new CardStrategy($this);
                break;
            case EnumsPaymentMethod::PAYMENT_METHOD_APPLE_PAY:
                return new ApplePayStrategy($this);
                break;
        }
    }
}
