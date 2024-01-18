<?php

namespace App\Services\Payment;

use App\Helpers\PaymentHelper;

class PaymentSource
{

    private $card_token;
    private $type;
    private $expiry_month;
    private $expiry_year;
    private $scheme;
    private $last4;
    private $fingerprint;
    private $bin;
    private $card_type;
    private $card_category;
    private $issuer;
    private $issuer_country;
    private $avs_check;
    private $cvv_check;
    private $payouts;
    private $fast_funds;

    function __construct($data = [])
    {
        if ($data) {
            foreach ($data as $key => $value) {
                $function = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
                if (method_exists($this, $function)) {
                    $this->$function($value);
                }
            }
        }
    }

    function setCardToken($card_token)
    {
        $this->card_token = $card_token;
    }

    function getCardToken()
    {
        return $this->card_token;
    }

    function setType($type)
    {
        $this->type = $type;
    }

    function getType()
    {
        return $this->type;
    }

    function setExpiryMonth($expiry_month)
    {
        $this->expiry_month = $expiry_month;
    }

    function getExpiryMonth()
    {
        return $this->expiry_month;
    }

    function setExpiryYear($expiry_year)
    {
        $this->expiry_year = $expiry_year;
    }

    function getExpiryYear()
    {
        return $this->expiry_year;
    }

    function setScheme($scheme)
    {
        $this->scheme = $scheme;
    }

    function getScheme()
    {
        return $this->scheme;
    }

    function setLast4($last4)
    {
        $this->last4 = $last4;
    }

    function getLast4()
    {
        return $this->last4;
    }

    function setFingerprint($fingerprint)
    {
        $this->fingerprint = $fingerprint;
    }

    function getFingerprint()
    {
        return $this->fingerprint;
    }

    function setBin($bin)
    {
        $this->bin = $bin;
    }

    function getBin()
    {
        return $this->bin;
    }

    function setCardType($card_type)
    {
        $this->card_type = $card_type;
    }

    function getCardType()
    {
        return $this->card_type;
    }

    function setCardCategory($card_category)
    {
        $this->card_category = $card_category;
    }

    function getCardCategory()
    {
        return $this->card_category;
    }

    function setIssuer($issuer)
    {
        $this->issuer = $issuer;
    }

    function getIssuer()
    {
        return $this->issuer;
    }

    function setIssuerCountry($issuer_country)
    {
        $this->issuer_country = $issuer_country;
    }

    function getIssuerCountry()
    {
        return $this->issuer_country;
    }

    function setAvsCheck($avs_check)
    {
        $this->avs_check = $avs_check;
    }

    function getAvsCheck()
    {
        return $this->avs_check;
    }

    function setCvvCheck($cvv_check)
    {
        $this->cvv_check = $cvv_check;
    }

    function getCvvCheck()
    {
        return $this->cvv_check;
    }

    function setPayouts($payouts)
    {
        $this->payouts = $payouts;
    }

    function getPayouts()
    {
        return $this->payouts;
    }

    function setFastFunds($fast_funds)
    {
        $this->fast_funds = $fast_funds;
    }

    function getFastFunds()
    {
        return $this->fast_funds;
    }

    function isMada() : bool {
        return PaymentHelper::isMadaCard($this->getBin());
    }

    function __toString()
    {
        return json_encode($this->toArray());
    }

    function toArray(): array
    {
        $data = get_class_vars(self::class);
        foreach ($data as $key => $value) {
            try {
                if ($this->$key == null) {
                    unset($data[$key]);
                } else {
                    $data[$key] = $this->$key;
                }
            } catch (\Throwable $th) {
            }
        }
        return $data;
    }
}
