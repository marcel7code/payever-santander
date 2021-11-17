<?php

namespace Payever\Santander\Payment;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Payever\Santander\Exceptions\SantanderRequestException;
use Payever\Santander\Exceptions\SantanderValidationException;
use Payever\Santander\Payment\Request\SantanderPayment;
use Payever\Santander\Payment\Response\SantanderOrder;

/**
 * @see https://docs.payever.org/payments/santander-installments
 */
class SantanderGateway
{
    const API_URL_LIVE = 'https://proxy.payever.org';
    const API_URL_SANDBOX = 'https://proxy.staging.devpayever.com';

    const SCOPE_CREATE_PAYMENT = 'API_CREATE_PAYMENT';
    const SCOPE_PAYMENT_ACTIONS = 'API_PAYMENT_ACTIONS';
    const GRANT_TYPE = 'http://www.payever.de/api/payment';

    /** @var Client */
    protected $client;
    /** @var string */
    protected $clientId;
    /** @var string */
    protected $secret;
    /** @var string */
    protected $tokenPaymentCreate;
    /** @var string */
    protected $tokenPaymentActions;

    public function __construct()
    {
        $this->clientId = config('santander_payment.client_id');
        $this->secret = config('santander_payment.client_secret');
        $this->client = new Client([
            'base_uri' => static::checkoutSrc(),
        ]);
    }

    /**
     * Create a new payment and return the redirect_url URL.
     *
     * @throws SantanderValidationException
     * @throws SantanderRequestException
     */
    public function makePayment(SantanderPayment $payment): string
    {
        $result = $this->post('/api/payment', $payment->getData(), true);
        $redirectUrl = $result['redirect_url'] ?? null;
        if (!$redirectUrl) {
            throw new SantanderRequestException('Cannot find redirect_url key in /api/payment response.');
        }

        return $redirectUrl;
    }

    /**
     * @throws SantanderRequestException
     * @throws SantanderValidationException
     */
    public function getPayment(string $paymentId): SantanderOrder
    {
        $result = $this->get("/api/payment/{$paymentId}")['result'] ?? [];

        return $this->getOrderFromResult($result);
    }

    /**
     * @throws SantanderRequestException
     * @throws SantanderValidationException
     */
    public function shippingGoods(string $paymentId): SantanderOrder
    {
        $result = $this->post("/api/payment/shipping-goods/{$paymentId}")['result'] ?? [];

        return $this->getOrderFromResult($result);
    }

    /**
     * @throws SantanderRequestException
     * @throws SantanderValidationException
     */
    public function refund(string $paymentId): SantanderOrder
    {
        $result = $this->post("/api/payment/refund/{$paymentId}")['result'] ?? [];

        return $this->getOrderFromResult($result);
    }

    /**
     * @throws SantanderRequestException
     * @throws SantanderValidationException
     */
    public function cancel(string $paymentId): SantanderOrder
    {
        $result = $this->post("/api/payment/cancel/{$paymentId}")['result'] ?? [];

        return $this->getOrderFromResult($result);
    }

    private function getOrderFromResult(array $result): SantanderOrder
    {
        foreach (['id','reference','status'] as $key) {
            if (!($result[$key] ?? null)) {
                throw new SantanderValidationException("getPayment result->{$key} is missing.");
            }
        }

        $order = new SantanderOrder;
        $order->setPaymentId($result['id']);
        $order->setReference($result['reference']);
        $order->setStatus($result['status']);
        $order->setSpecificStatus($result['specific_status']);

        return $order;
    }

    /**
     * @throws SantanderRequestException
     */
    private function post(string $uri, array $data = [], bool $isPaymentCreate = false): array
    {
        $token = $isPaymentCreate ? $this->getAccessTokenForCreatePayment() : $this->getAccessTokenForPaymentActions();

        try {
            $response = $this->client->post($uri, [
                'debug' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                    'Content-type' => 'application/json',
                ],
                'Accept: application/json',
                'json' => $data,
            ]);
        } catch (GuzzleException $e) {
            $this->tokenPaymentActions = null;
            $this->tokenPaymentCreate = null;
            throw new SantanderRequestException($e->getMessage(), 0, $e);
        }

        return json_decode($response->getBody()->getContents(), true) ?: [];
    }

    /**
     * @throws SantanderRequestException
     */
    private function get(string $uri, array $data = []): array
    {
        try {
            $response = $this->client->get($uri, [
                'debug' => false,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->getAccessTokenForPaymentActions(),
                    'Content-type' => 'application/json',
                ],
                'Accept: application/json',
                'json' => $data,
            ]);
        } catch (GuzzleException $e) {
            throw new SantanderRequestException($e->getMessage(), 0, $e);
        }

        return json_decode($response->getBody()->getContents(), true) ?: [];
    }

    /**
     * @throws SantanderRequestException
     */
    private function getAccessTokenForCreatePayment(): string
    {
        if (!$this->tokenPaymentCreate) {
            $this->tokenPaymentCreate = $this->getAccessToken(self::SCOPE_CREATE_PAYMENT);
        }

        return $this->tokenPaymentCreate;
    }

    /**
     * @throws SantanderRequestException
     */
    private function getAccessTokenForPaymentActions(): string
    {
        if (!$this->tokenPaymentActions) {
            $this->tokenPaymentActions = $this->getAccessToken(self::SCOPE_PAYMENT_ACTIONS);
        }

        return $this->tokenPaymentActions;
    }

    /**
     * @throws SantanderRequestException
     */
    private function getAccessToken(string $scope): string
    {
        try {
            $response = $this->client->post(static::checkoutSrc() . '/oauth/v2/token', [
                'debug' => false,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'Accept: application/json',
                'json' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->secret,
                    'grant_type' => self::GRANT_TYPE,
                    'scope' => $scope,
                ],
            ]);
        } catch (GuzzleException $e) {
            throw new SantanderRequestException($e->getMessage(), 0, $e);
        }

        $body = json_decode($response->getBody()->getContents(), true) ?: [];
        $token = $body['access_token'] ?? null;
        if (!$token) {
            throw new SantanderRequestException('Cannot find access_token key in response.');
        }

        return $token;
    }

    private static function checkoutSrc(): string
    {
        if (config('santander_payment.testing')) {
            return self::API_URL_SANDBOX;
        }

        return self::API_URL_LIVE;
    }
}
