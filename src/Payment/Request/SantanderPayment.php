<?php

namespace Payever\Santander\Payment\Request;

use Payever\Santander\Exceptions\SantanderValidationException;

class SantanderPayment
{
    const CHANNEL = 'api';
    const CURRENCY = 'DKK';
    const PAYMENT_METHOD = 'santander_installment_dk';

    /** @var string */
    private $channel = self::CHANNEL;

    /** @var float */
    private $amount;

    /** @var float|null */
    private $fee = null;

    /** @var string */
    private $orderId;

    /** @var string */
    private $currency = self::CURRENCY;

    /** @var array */
    private $cart = [];

    /** @var string */
    private $paymentMethod = self::PAYMENT_METHOD;

    /** @var SantanderCustomer */
    private $customer;

    /** @var string|null */
    private $hostUrl;

    public function getChannel(): string
    {
        return $this->channel;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    public function getFee(): ?float
    {
        return $this->fee;
    }

    public function setFee(?float $fee): void
    {
        $this->fee = $fee;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function setOrderId(string $orderId): void
    {
        $this->orderId = $orderId;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCart(): array
    {
        return $this->cart;
    }

    public function addCartItem(SantanderCartItem $santanderCartItem): void
    {
        $this->cart[] = $santanderCartItem->getData();
    }

    public function getPaymentMethod(): string
    {
        return $this->paymentMethod;
    }

    public function getCustomer(): SantanderCustomer
    {
        return $this->customer;
    }

    public function setCustomer(SantanderCustomer $customer): void
    {
        $this->customer = $customer;
    }

    public function getHostUrl(): string
    {
        return $this->hostUrl ?: config('app.url');
    }

    public function setHostUrl(string $hostUrl): void
    {
        $this->hostUrl = $hostUrl;
    }

    public function validate(): void
    {
        foreach ([
            'channel',
            'amount',
            'orderId',
            'currency',
            'paymentMethod',
            'customer',
            'cart',
        ] as $field) {
            if (!$this->$field) {
                throw new SantanderValidationException('SantanderPayment is not complete. Missing '.$field);
            }
        }
        if (strlen($this->orderId) > 50) {
            throw new SantanderValidationException('SantanderPayment is having an exception: orderId cannot exceed 50 chars.');
        }
    }

    private function getCartData(): array
    {
        return collect($this->getCart())->map(function (SantanderCartItem $cartItem) {
            return $cartItem->getData();
        })->all();
    }

    public function getSuccessUrl(): string
    {
        // todo
        return 'http://127.0.0.1:8000/success';
    }

    public function getPendingUrl(): string
    {
        // todo
        return 'http://127.0.0.1:8000/pending';
    }

    public function getFailureUrl(): string
    {
        // todo
        return 'http://127.0.0.1:8000/failure';
    }

    public function getCancelUrl(): string
    {
        // todo
        return 'http://127.0.0.1:8000/cancel';
    }

    public function getNoticeUrl(): string
    {
        return "{$this->getHostUrl()}/orders/{$this->getOrderId()}/notify";
    }

    /**
     * @throws SantanderValidationException
     */
    public function getData(): array
    {
        $this->validate();

        $result = array_merge([
            'channel' => $this->getChannel(),
            'amount' => $this->getAmount(),
            'order_id' => $this->getOrderId(),
            'currency' => $this->getCurrency(),
            'payment_method' => $this->getPaymentMethod(),

            'success_url' => $this->getSuccessUrl(),
            'pending_url' => $this->getPendingUrl(),
            'failure_url' => $this->getFailureUrl(),
            'cancel_url' => $this->getCancelUrl(),

            'notice_url' => $this->getNoticeUrl(),
        ], $this->customer->getData());

        $result['cart'] = json_encode($this->getCartData());

        if (($value = $this->getFee()) !== null) {
            $result['fee'] = $value;
        }

        return $result;
    }
}
