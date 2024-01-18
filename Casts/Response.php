<?php

namespace App\Services\Payment\Casts;

use App\Services\Payment\PaymentResponse;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Response implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        $paymentResponse =  new PaymentResponse(json_decode($value, true));
        // $response = $value;
        // $paymentResponse->setMessage(data_get($response, 'message'));
        // $paymentResponse->setStatusCode(data_get($response, 'status_code'));
        // $paymentResponse->setErrorDetails(data_get($response, 'error_details'));
        // $paymentResponse->setLinks(data_get($response, 'links'));
        return $paymentResponse;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        if (!$value instanceof PaymentResponse) {
            throw new InvalidArgumentException('The given value is not an PaymentResponse instance.');
        }
        return json_encode($value->toArray());
    }
}
