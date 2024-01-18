<?php

namespace App\Services\Payment\Gateways\Checkout;

use App\Services\Payment\Enums\PaymentTransactionStatus;
use App\Services\Payment\PaymentResponse;
use App\Services\Payment\PaymentSource;
use Checkout\CheckoutApiException;
use Checkout\CheckoutAuthorizationException;
use Illuminate\Support\Arr;

final class ResponseMapper
{
    /**
     * 
     * @var CheckoutApiException|CheckoutAuthorizationException|array
     */
    private $response;

    private PaymentResponse $paymentResponse;

    function __construct(&$paymentResponse)
    {
        $this->paymentResponse = $paymentResponse;
    }

    function map($resonse): void
    {
        $this->response = $resonse;
        if ($this->response instanceof CheckoutApiException) {
            $this->mapCheckoutApiException();
        } elseif ($this->response instanceof CheckoutAuthorizationException) {
            $this->mapCheckoutAuthorizationException();
        } elseif (is_array($this->response)) {
            $this->mapArray();
        }
    }

    function mapCheckoutApiException(): void
    {
        $this->paymentResponse->setStatus(PaymentTransactionStatus::STATUS_FINISHED);
        $this->paymentResponse->setProcessedOn(now());
        $this->paymentResponse->setApproved(false);
        $this->paymentResponse->setVendorStatus(CheckOutPaymentService::STATUS_DECLINED);
        $this->paymentResponse->setStatusCode(isset($this->response->http_metadata) ? $this->response->http_metadata->getStatusCode() : null);
        $this->paymentResponse->setErrorDetails($this->response->error_details);
        $this->paymentResponse->setMessage(Arr::first(data_get($this->response->error_details, 'error_codes')));
    }
    function mapCheckoutAuthorizationException(): void
    {
        $this->paymentResponse->setStatus(PaymentTransactionStatus::STATUS_FINISHED);
        $this->paymentResponse->setProcessedOn(now());
        $this->paymentResponse->setApproved(false);
        $this->paymentResponse->setVendorStatus(CheckOutPaymentService::STATUS_DECLINED);
        $this->paymentResponse->setStatusCode(422);
        $this->paymentResponse->setMessage($this->response->getMessage());
    }
    function mapArray(): void
    {
        $this->paymentResponse->setReference(data_get($this->response, 'id'));
        $this->paymentResponse->setStatus($this->mapStatus(data_get($this->response, 'status')));
        $this->paymentResponse->setApproved(data_get($this->response, 'approved'));
        $this->paymentResponse->setVendorStatus(data_get($this->response, 'status'));
        $this->paymentResponse->setProcessedOn(data_get($this->response, 'requested_on'));
        $this->paymentResponse->setStatusCode(optional(data_get($this->response, 'http_metadata'))->getStatusCode());
        $this->paymentResponse->setVoidLink(data_get($this->response, '_links.void'));
        $this->paymentResponse->setRefundLink(data_get($this->response, '_links.refund'));
        $this->paymentResponse->setCaptureLink(data_get($this->response, '_links.capture'));
        $this->paymentResponse->setRedirectLink(data_get($this->response, '_links.redirect'));
        $this->paymentResponse->setSource($this->mapSource());
    }

    private function mapSource(): PaymentSource
    {
        $paymentSource = new PaymentSource();
        $paymentSource->setCardToken(data_get($this->response, 'source.id'));
        $paymentSource->setType(data_get($this->response, 'source.type'));
        $paymentSource->setExpiryMonth(data_get($this->response, 'source.expiry_month'));
        $paymentSource->setExpiryYear(data_get($this->response, 'source.expiry_year'));
        $paymentSource->setScheme(data_get($this->response, 'source.scheme'));
        $paymentSource->setLast4(data_get($this->response, 'source.last4'));
        $paymentSource->setFingerprint(data_get($this->response, 'source.fingerprint'));
        $paymentSource->setBin(data_get($this->response, 'source.bin'));
        $paymentSource->setCardType(data_get($this->response, 'source.card_type'));
        $paymentSource->setCardCategory(data_get($this->response, 'source.card_category'));
        $paymentSource->setIssuer(data_get($this->response, 'source.issuer'));
        $paymentSource->setIssuerCountry(data_get($this->response, 'source.issuer_country'));
        $paymentSource->setAvsCheck(data_get($this->response, 'source.avs_check'));
        $paymentSource->setCvvCheck(data_get($this->response, 'source.cvv_check'));
        $paymentSource->setPayouts(data_get($this->response, 'source.payouts'));
        $paymentSource->setFastFunds(data_get($this->response, 'source.fast_funds'));
        return $paymentSource;
    }

    private function mapStatus($status): PaymentTransactionStatus|null
    {
        if ($status == CheckOutPaymentService::STATUS_PENDING) {
            return PaymentTransactionStatus::STATUS_PENDING;
        } else {
            return PaymentTransactionStatus::STATUS_FINISHED;
        }
    }
}
