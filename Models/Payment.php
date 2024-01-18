<?php

namespace App\Services\Payment\Models;

use App\Services\Payment\Enums\PaymentAction;
use App\Services\Payment\Enums\PaymentMethodAction;
use App\Services\Payment\Enums\PaymentStatus;
use App\Services\Payment\Services\HandleSuccessfulPaymentService;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use SoftDeletes, HasTimestamps;

    const SUCCEED_STATUS = [
        PaymentStatus::STATUS_SUCCEED,
        PaymentStatus::STATUS_REVERTED,
    ];

    const FAILED_STATUS = [
        PaymentStatus::STATUS_FAILED_PROCEED,
        PaymentStatus::STATUS_FAILED_REVERT,
    ];

    protected $fillable = [
        'status',
        'action',
        'reference',
        'payable',
        'payer',
        'currency_id',
        'amount',
        'refunded_amount',
        'is_refundable',
        'last_response',
        'processed_at',
        'meta',
    ];

    protected $casts = [
        'status' => PaymentStatus::class,
        'action' => PaymentAction::class,
        'last_response' => \App\Services\Payment\Casts\Response::class,
        'meta' => 'array'
    ];

    public function reference()
    {
        return $this->morphTo();
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function payer()
    {
        return $this->morphTo();
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function succeedPaymentMethods()
    {
        return $this->hasMany(PaymentMethod::class)->orderBy('sort')->isProcessed();
    }

    public function currentProcessingPayment()
    {
        return $this->hasOne(PaymentMethod::class)->orderBy('sort')->isProceedable();
    }

    function isProceedable(): bool
    {
        return in_array($this->status, [
            PaymentStatus::STATUS_INIT,
            PaymentStatus::STATUS_PENDING,
        ]);
    }

    function isAllTransactionsSucceed(): bool
    {
        return $this->paymentMethods->where('status', PaymentStatus::STATUS_SUCCEED)->count() == $this->paymentMethods->where()->count();
    }

    function isPending(): bool
    {
        return $this->status->value == PaymentStatus::STATUS_PENDING;
    }

    function isProcessed(): bool
    {
        return $this->status->value == PaymentStatus::STATUS_SUCCEED;
    }

    function isReverted(): bool
    {
        return $this->status->value == PaymentStatus::STATUS_REVERTED;
    }

    function isFinished(): bool
    {
        return $this->isProcessed() || $this->isReverted();
    }

    function isFailedProcessing(): bool
    {
        return $this->status->value == PaymentStatus::STATUS_FAILED_PROCEED;
    }

    function isFailedReverting(): bool
    {
        return $this->status->value == PaymentStatus::STATUS_FAILED_REVERT;
    }

    function isAddCardType(): bool
    {
        return $this->action == PaymentAction::ACTION_ADD_CARD;
    }

    function markAsProcessed()
    {
        $this->update([
            'status' => PaymentStatus::STATUS_SUCCEED
        ]);
    }

    function markAsFailedProceed()
    {
        $this->update([
            'status' => PaymentStatus::STATUS_FAILED_PROCEED
        ]);
    }

    function markAsFailedRevert()
    {
        $this->update([
            'status' => PaymentStatus::STATUS_FAILED_REVERT
        ]);
    }

    function updateStatusIfNeeded(PaymentMethod $paymentMethod)
    {
        $data = [
            'last_response' => $paymentMethod->last_response
        ];

        if ($paymentMethod->isPending()) {
            $data['status']  = PaymentStatus::STATUS_PENDING;
        } else {
            $data['processed_at'] = $paymentMethod->processed_at;
            switch ($paymentMethod->action) {
                case PaymentMethodAction::ACTION_TYPE_PAY:
                    if ($paymentMethod->isSucceed()) {
                        $data['status'] = PaymentStatus::STATUS_SUCCEED;
                    } else {
                        $data['status'] = PaymentStatus::STATUS_FAILED_PROCEED;
                    }
                    break;
                case PaymentMethodAction::ACTION_TYPE_RETURN:
                case PaymentMethodAction::ACTION_TYPE_PRTIAL_RETURN:
                    if ($paymentMethod->isSucceed()) {
                        $data['status'] = PaymentStatus::STATUS_REVERTED;
                    } else {
                        $data['status'] = PaymentStatus::STATUS_FAILED_REVERT;
                    }
                    break;
            }
        }
        $this->update($data);
    }

    static function boot()
    {
        parent::boot();
        static::updated(function (self $payment) {
            // TODO: First Update RTDB
            if ($payment->isPending()) {
            }
            if ($payment->isProcessed()) {
                app(HandleSuccessfulPaymentService::class, ['payment' => $payment])->execute();
            }
            if ($payment->isReverted()) {
            }
            if ($payment->isFailedProcessing()) {
            }
            if ($payment->isFailedReverting()) {
            }
        });
    }

    function getTrackingKeyAttribute(): string
    {
        return $this->id . '-' . $this->updated_at->unix();
    }
}
