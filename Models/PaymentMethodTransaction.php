<?php

namespace App\Services\Payment\Models;

use App\Services\Payment\Enums\PaymentMethodStatus;
use App\Services\Payment\Enums\PaymentTransactionStatus;
use App\Services\Payment\Enums\PaymentTransactionType;
use App\Services\Payment\Events\PaymentTransactionProcessed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;

/**
 * @property \Carbon\Carbon $requested_on
 * @property \App\Services\Payment\PaymentResponse $response
 */
class PaymentMethodTransaction extends Model
{
    use HasFactory;

    const PROCEED_TYPES = [
        PaymentTransactionType::ACTION_TYPE_PURCHASE,
        PaymentTransactionType::ACTION_TYPE_AUTHORIZATION,
        PaymentTransactionType::ACTION_TYPE_CAPTURE,
        PaymentTransactionType::ACTION_TYPE_DEPOSIT
    ];

    const REVERT_TYPES = [
        PaymentTransactionType::ACTION_TYPE_REFUND,
        PaymentTransactionType::ACTION_TYPE_VOID,
        PaymentTransactionType::ACTION_TYPE_WITHDRAW,
    ];

    protected $fillable = [
        'action',
        'reference',
        'status',
        'vendor_status',
        'vendor',
        'payment_method_id',
        'request',
        'response',
        'processed_at',
        'approved',
    ];

    protected $casts = [
        'action' => PaymentTransactionType::class,
        'request' => AsArrayObject::class,
        'response' => \App\Services\Payment\Casts\Response::class,
        'processed_at' => 'datetime',
        'status' => PaymentTransactionStatus::class
    ];

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function transaction()
    {
        return $this->morphTo();
    }

    function scopeWherePaymentId($query, $paymentId)
    {
        return $query->where('reference', $paymentId)->orderByRaw("processed_at DESC NULLS LAST");
    }

    function isPending(): bool
    {
        return $this->status ==  PaymentTransactionStatus::STATUS_PENDING;
    }

    function isSucceed(): bool
    {
        return $this->status == PaymentTransactionStatus::STATUS_FINISHED && $this->approved;
    }

    function isFailed(): bool
    {
        return $this->status == PaymentTransactionStatus::STATUS_FINISHED && !$this->approved;
    }

    function isProcessed(): bool
    {
        return in_array($this->action, self::PROCEED_TYPES) && $this->status == PaymentTransactionStatus::STATUS_FINISHED && $this->approved;
    }

    function isReverted(): bool
    {
        return in_array($this->action, self::REVERT_TYPES) && $this->status == PaymentTransactionStatus::STATUS_FINISHED && $this->approved;
    }

    function isFailedProceed(): bool
    {
        return in_array($this->action, self::PROCEED_TYPES) && $this->status == PaymentTransactionStatus::STATUS_FINISHED && !$this->approved;
    }

    function isFailedRevert(): bool
    {
        return in_array($this->action, self::REVERT_TYPES) && $this->status == PaymentTransactionStatus::STATUS_FINISHED && !$this->approved;
    }

    function getPaymentMethodStatusBasedOnCurrentStatus(): PaymentMethodStatus
    {
        if ($this->isPending()) {
            return PaymentMethodStatus::STATUS_PENDING;
        }
        if ($this->isSucceed() || $this->isReverted()) {
            return PaymentMethodStatus::STATUS_SUCCEED;
        }
        if ($this->isFailedProceed() || $this->isFailedRevert()) {
            return PaymentMethodStatus::STATUS_FAILED;
        }
    }

    static function boot()
    {
        parent::boot();
        static::created(function (self $transaction) {
            $transaction->paymentMethod->update([
                'status' => $transaction->getPaymentMethodStatusBasedOnCurrentStatus(),
                'processed_at' => $transaction->processed_at,
                'last_response' => $transaction->response,
            ]);
        });
    }

    function canOverride($datetime): bool
    {
        return empty($this->processed_at) || $this->processed_at->lt($datetime);
    }
}
