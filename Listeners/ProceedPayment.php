<?php

namespace App\Services\Payment\Listeners;

use App\Services\Payment\Events\PaymentCreated;
use App\Services\Payment\Services\ProceedPayment as ServicesProceedPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ProceedPayment // implements ShouldQueue
{
    // use Queueable, InteractsWithQueue;
    /**
     * Handle the event.
     */
    public function handle(PaymentCreated $event): void
    {
        app(ServicesProceedPayment::class, ['payment' => $event->payment])->execute();
    }
}
