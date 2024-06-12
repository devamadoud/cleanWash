<?php
namespace App\Services;

use GuzzleHttp\Client;
use Kkiapay\Kkiapay;
use Paydunya\Paydunya;

class PayOutService
{
    private $dunyaMasterApiKey;
    private $dunyaPrivateApiKey;
    private $dunyaPublicApiKey;
    private $dunyaBaseUrl;
    private $dunyaToken;
    private $client;

    private $kkiapayPublicKey;
    private $kkiapayPrivateKey;
    private $kkiapaySecret;

    public function __construct(
        string $kkiapayPublicKey, string $kkiapayPrivateKey, string $kkiapaySecret, 
        string $dunyaMasterApiKey, string $dunyaPrivateApiKey, string $dunyaPublicApiKey, 
        string $dunyaBaseUrl, string $dunyaToken
    )
    {
        $this->client = new Client();
        $this->dunyaMasterApiKey = $dunyaMasterApiKey;
        $this->dunyaPrivateApiKey = $dunyaPrivateApiKey;
        $this->dunyaPublicApiKey = $dunyaPublicApiKey;
        $this->dunyaBaseUrl = $dunyaBaseUrl;
        $this->dunyaToken = $dunyaToken;

        $this->kkiapayPublicKey = $kkiapayPublicKey;
        $this->kkiapayPrivateKey = $kkiapayPrivateKey;
        $this->kkiapaySecret = $kkiapaySecret;
    }
    public function payDunyaOut($phoneNumber, $amount, )
    {
        $body = [
            "recipient_phone" => $phoneNumber,
            "amount" => $amount,
            "support_fees" => 1,
            "send_notification" => 1
        ];

        $options = [
            'headers' => [
                "Content-Type" => "application/json",
                "PAYDUNYA-MASTER-KEY" => $this->dunyaMasterApiKey,
                "PAYDUNYA-PRIVATE-KEY" => $this->dunyaPrivateApiKey,
                "PAYDUNYA-PUBLIC-KEY" => $this->dunyaPublicApiKey,
                "PAYDUNYA-TOKEN" => $this->dunyaToken
            ],
            'json' => $body
        ];

        $response = $this->client->request('POST', $this->dunyaBaseUrl, $options);
        return $response;
    }

    public function config(){
        $paydunyaSetup = new \Paydunya\Setup();

        $paydunyaSetup::setMasterKey('Y19LihFj-ktjg-6oM9-7v6j-wnjdUTCJqtcO');
        $paydunyaSetup::setPublicKey('test_public_rrTo5MHUGavFqbAdOtDSim7OkIR');
        $paydunyaSetup::setPrivateKey('test_private_gP2PchdkBqH0e0k3D03F9derpL6');
        $paydunyaSetup::setToken('8Dnm6vTenonfp7dYrKNS');
        $paydunyaSetup::setMode('test');
        
        return $paydunyaSetup;
    }

    public function setStore(){
        $paydunyaStore = new \Paydunya\Checkout\Store();

        $paydunyaStore::setName('Setsetal service');
        $paydunyaStore::setTagline('Tout propre toute de suite.');
        $paydunyaStore::setPhoneNumber('783841245');
        $paydunyaStore::setPostalAddress('Dakar Liberte 4 - Dakar');
        $paydunyaStore::setWebsiteUrl('https://setsetal.com');
        $paydunyaStore::setCallbackUrl('');

        return $paydunyaStore;
    }

    public function kkiaPayOut(){
        $kkiapay = new Kkiapay($this->kkiapayPublicKey, $this->kkiapayPrivateKey, $this->kkiapaySecret, true);
        return $kkiapay;
    }
}