<?php

namespace App\Services\Payment\Events;

use App\Services\Payment\Models\PaymentMethodTransaction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentTransactionProcessed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public PaymentMethodTransaction $paymentTransaction;

    /**
     * Create a new event instance.
     */
    public function __construct(PaymentMethodTransaction $paymentTransaction)
    {
        $this->paymentTransaction = $paymentTransaction;
    }
}
