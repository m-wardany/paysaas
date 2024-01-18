<?php

namespace App\Services\Payment\Listeners;

use App\Services\Payment\Events\PaymentTransactionProcessed;
use App\Services\Payment\Services\CreateCardForTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AddNewCard // implements ShouldQueue
{
    // use Queueable, InteractsWithQueue;
    /**
     * Handle the event.
     */
    public function handle(PaymentTransactionProcessed $event): void
    {
        $paymentTransaction = $event->paymentTransaction;
        if ($paymentTransaction->paymentMethod->payment->isAddCardType()) {
            app(CreateCardForTransaction::class, ['paymentTransaction' => $paymentTransaction])->execute();
        }
    }
}
