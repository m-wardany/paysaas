<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\Lang;

class PaymentResponse
{
    private $statusCode;

    private $message;

    private $errorDetails;

    private $links = [];

    private $request = [];

    private $vendor;

    private $status;

    private $vendorStatus;

    private $reference;

    private $approved;

    private $processedOn;

    private PaymentSource|null $source;

    function __construct($data = null)
    {
        if ($data) {
            $this->setStatusCode(data_get($data, 'statusCode'));

            $this->setMessage(data_get($data, 'message'));

            $this->setErrorDetails(data_get($data, 'errorDetails'));

            $this->setRequest(data_get($data, 'request'));

            $this->setVendor(data_get($data, 'vendor'));

            $this->setStatus(data_get($data, 'status'));

            $this->setVendorStatus(data_get($data, 'vendorStatus'));

            $this->setReference(data_get($data, 'reference'));

            $this->setApproved(data_get($data, 'approved'));

            $this->setProcessedOn(data_get($data, 'processedOn'));

            $this->setSource(data_get($data, 'source') ? new PaymentSource(data_get($data, 'source')) : null);

            $this->setRedirectLink(data_get($data, 'links.redirect'));

            $this->setVoidLink(data_get($data, 'links.void'));

            $this->setRefundLink(data_get($data, 'links.refund'));

            $this->setCaptureLink(data_get($data, 'links.capture'));
        }
        else {
            $this->source = null;
        }
    }

    function getStatusCode()
    {
        return $this->statusCode;
    }

    function getMessage()
    {
        $message = 'payment.' . $this->message;
        return Lang::has($message) ? __($message) : $this->message;
    }

    function getErrorDetails()
    {
        return $this->errorDetails;
    }

    function getRedirectLink()
    {
        return data_get($this->links, 'redirect');
    }

    function getVoidLink()
    {
        return data_get($this->links, 'void');
    }

    function getRefundLink()
    {
        return data_get($this->links, 'refund');
    }

    function getCaptureLink()
    {
        return data_get($this->links, 'capture');
    }

    function getRequest()
    {
        return $this->request;
    }

    function getVendor()
    {
        return $this->vendor;
    }

    function getVendorStatus()
    {
        return $this->vendorStatus;
    }

    /**
     * 
     * @return \App\Services\Payment\Enums\PaymentTransactionStatus
     */
    function getStatus()
    {
        return $this->status;
    }

    function getReference()
    {
        return $this->reference;
    }

    function getIsApproved()
    {
        return $this->approved;
    }

    function getProcessedOn()
    {
        return $this->processedOn;
    }

    function getSource(): PaymentSource | null
    {
        return $this->source;
    }

    function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    }

    function setMessage($message)
    {
        $this->message = $message;
    }

    function setErrorDetails($errorDetails)
    {
        $this->errorDetails = $errorDetails;
    }

    function setLinks($links)
    {
        $this->links = $links;
    }

    function setRequest($request)
    {
        $this->request = $request;
    }

    function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    function setVendorStatus($vendorStatus)
    {
        $this->vendorStatus = $vendorStatus;
    }

    function setStatus($status)
    {
        $this->status = $status;
    }

    function setReference($reference)
    {
        $this->reference = $reference;
    }

    function setRedirectLink($redirect)
    {
        $this->links['redirect'] = $redirect;
    }

    function setVoidLink($void)
    {
        $this->links['void'] = $void;
    }

    function setRefundLink($refund)
    {
        $this->links['refund'] = $refund;
    }

    function setCaptureLink($capture)
    {
        $this->links['capture'] = $capture;
    }

    function setApproved($approved)
    {
        $this->approved = $approved;
    }

    function setProcessedOn($processedOn)
    {
        $this->processedOn = $processedOn;
    }

    function setSource(PaymentSource|null $source)
    {
        $this->source = $source;
    }

    function __toString()
    {
        return json_encode($this->toArray());
    }

    function toArray(): array
    {
        $data = [];
        if ($this->statusCode !== null) {
            $data['statusCode'] = $this->statusCode;
        }
        if ($this->message !== null) {
            $data['message'] = $this->message;
        }
        if ($this->errorDetails !== null) {
            $data['errorDetails'] = $this->errorDetails;
        }
        if ($this->vendor !== null) {
            $data['vendor'] = $this->vendor;
        }
        if ($this->status !== null) {
            $data['status'] = $this->status;
        }
        if ($this->vendorStatus !== null) {
            $data['vendorStatus'] = $this->vendorStatus;
        }
        if ($this->approved !== null) {
            $data['approved'] = $this->approved;
        }
        if ($this->processedOn !== null) {
            $data['processedOn'] = $this->processedOn;
        }

        if ($this->source !== null) {
            $data['source'] = $this->source->toArray();
        }

        $links = array_filter($this->links, function ($value) {
            return $value !== null;
        });

        if (!empty($links)) {
            $data['links'] = $links;
        }
        return $data;
    }
}
