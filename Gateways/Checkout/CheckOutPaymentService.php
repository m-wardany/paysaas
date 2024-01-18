<?php

namespace App\Services\Payment\Gateways\Checkout;

use App\Services\Payment\Enums\PaymentGateway;
use App\Services\Payment\Enums\PaymentTransactionStatus;
use App\Services\Payment\Interfaces\PaymentServiceInterface;
use App\Services\Payment\PaymentResponse;
use App\Services\Payment\PaymentSource;
use App\Services\Payment\Strategies\Token;
use Checkout\CheckoutApi;
use Checkout\CheckoutApiException;
use Checkout\CheckoutAuthorizationException;
use Checkout\CheckoutSdk;
use Checkout\Common\Currency;
use Checkout\Environment;
use Checkout\Payments\Request\PaymentRequest;
use Checkout\Payments\Request\Source\RequestTokenSource;
use Checkout\Payments\ThreeDsRequest;
use Illuminate\Support\Arr;

final class CheckOutPaymentService  implements PaymentServiceInterface
{
    const STATUS_CARD_VERIFIED          = 'Card Verified';
    const STATUS_AUTHORIZED             = 'Authorized';
    const STATUS_CAPTURED               = 'Captured';
    const STATUS_DECLINED               = 'Declined';
    const STATUS_PENDING                = 'Pending';
    const STATUS_REFUNDED               = 'Refunded';
    const STATUS_VOIDED                 = 'Voided';
    const STATUS_CANCELED               = 'Canceled';
    const STATUS_EXPIRED                = 'Expired';
    const STATUS_PAID                   = 'Paid';
    const STATUS_PARTIALLY_CAPTURED     = 'Partially Captured';
    const STATUS_PARTIALLY_REFUNDED     = 'Partially Refunded';

    private CheckoutApi $checkout;
    private PaymentResponse $paymentResponse;
    private $responseMapper;

    function __construct()
    {
        $this->checkout = CheckoutSdk::builder()
            ->staticKeys()
            ->environment(Environment::sandbox())
            ->secretKey(env('PROD_CHECKOUT_SECRET_KEY_NEW_INTEGRATION'))
            ->publicKey(env('PROD_CHECKOUT_PUBLIC_KEY_NEW_INTEGRATION'))
            ->build();

        $this->paymentResponse = new PaymentResponse();
        $this->paymentResponse->setVendor(PaymentGateway::Checkout);
        $this->responseMapper = new ResponseMapper($this->paymentResponse);
    }

    /**
     * @param Token $token
     * @return void
     */
    public function payWithToken(Token $token): PaymentResponse
    {
        $currency = optional($token->getCurrency())->code;

        $requestTokenSource = new RequestTokenSource();
        $requestTokenSource->token = $token->token();

        $request = new PaymentRequest();
        $request->source = $requestTokenSource;
        $request->capture = true;
        $request->reference = $token->reference();
        $request->amount = $this->resolveAmount($token->getAmount(), $currency);
        $request->currency = Currency::$$currency;
        $request->three_ds = new ThreeDsRequest();

        $this->paymentResponse->setRequest($this->resolveRequestDetails($request));
        try {
            $response = $this->checkout->getPaymentsClient()->requestPayment($request);
            $this->responseMapper->map($response);
        } catch (CheckoutApiException $e) {
            $this->responseMapper->map($e);
        } catch (CheckoutAuthorizationException $e) {
            $this->responseMapper->map($e);
        }

        return $this->paymentResponse;
    }

    function getPaymentDetails($paymentId): PaymentResponse
    {
        try {
            $response = $this->checkout->getPaymentsClient()->getPaymentDetails($paymentId);
            $this->responseMapper->map($response);
        } catch (CheckoutApiException $e) {
            $this->responseMapper->map($e);
        } catch (CheckoutAuthorizationException $e) {
            $this->responseMapper->map($e);
        }
        return $this->paymentResponse;
    }

    private function resolveAmount($amount, $currency = null): int
    {
        if (in_array(strtoupper($currency), config('checkout.TWO_DECIMAL_CURRENCIES'))) {
            return floor($amount * 100);
        }
        return floor($amount);
    }

    private function resolveRequestDetails(PaymentRequest $request): array
    {
        return [
            'source' => get_class($request->source),
            'capture' => $request->capture,
            'reference' => $request->reference,
            'amount' => $request->amount,
            'currency' => $request->currency,
            'three_ds' => !!$request->three_ds,
        ];
    }
}
