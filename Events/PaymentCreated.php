<?php

namespace App\Services\Payment\Events;

use App\Services\Payment\Models\Payment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Payment $payment;

    /**
     * Create a new event instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
