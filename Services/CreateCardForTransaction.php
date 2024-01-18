<?php

namespace App\Services\Payment\Services;

use App\Models\PaymentCard;
use App\Services\Payment\Models\Payment;
use App\Services\Payment\Models\PaymentMethodTransaction;
use App\Services\Payment\PaymentSource;
use Illuminate\Support\Facades\DB;

class CreateCardForTransaction
{
    private PaymentMethodTransaction $paymentTransaction;
    private Payment $payment;

    function __construct(PaymentMethodTransaction $paymentTransaction)
    {
        $this->paymentTransaction = $paymentTransaction;
        $this->payment = $paymentTransaction->paymentMethod->payment;
    }

    function execute()
    {
        $source = $this->paymentTransaction->response->getSource();
        if ($source instanceof PaymentSource && !$this->payment->payable) {
            $input = $source->toArray();
            $input['status'] = PaymentCard::STATUS_CONFIRMED;
            $input['previous_payment_id'] = $this->paymentTransaction->reference;
            $input['customer_id'] = $this->payment->payer_id;
            $input['card_holder'] = data_get($this->payment, 'card_holder');

            if ($source->isMada()) {
                $input['three_ds_required'] = true;
                $input['cvv_required'] = true;
            }

            $similar_card = PaymentCard::isDuplicated($source->getFingerprint(), $this->payment->payer_id);
            if ($similar_card) {
                $input['status'] = PaymentCard::STATUS_DUPLICATED;
            }

            DB::beginTransaction();
            $card = PaymentCard::create($input);
            $this->payment->payable()->associate($card);
            $this->payment->save();
            DB::commit();
        }
    }
}
