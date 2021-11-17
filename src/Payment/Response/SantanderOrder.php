<?php

namespace Payever\Santander\Payment\Response;

class SantanderOrder
{
    // Customer has completed and sent the form, but Santander’s API hasn’t responded with any decision/status yet.
    const STATUS_NEW = 'STATUS_NEW';
    // The decision has not been made yet, or the customer has passed the automatic credit scoring process,
    //  but hasn’t completed the identification process yet/ needs to provide further details.
    const STATUS_IN_PROCESS = 'STATUS_IN_PROCESS';
    // The request is approved, all documents/ required information have been submitted and the final decision has been made. The order is ready to ship.
    const STATUS_ACCEPTED = 'STATUS_ACCEPTED';
    // Shipping goods have been triggered successfully, Santander has been notified and the transaction has been activated.
    const STATUS_PAID = 'STATUS_PAID';
    // The transaction has been cancelled by the merchant.
    const STATUS_CANCELLED = 'STATUS_CANCELLED';
    // The transaction has been refunded by the merchant.
    const STATUS_REFUNDED = 'STATUS_REFUNDED';
    // The transaction has either failed, was declined, expired or cancelled.
    const STATUS_FAILED = 'STATUS_FAILED';
    // The customer has been declined by the payment provider.
    const STATUS_DECLINED = 'STATUS_DECLINED';

    /**
     * This is the Santander order ID.
     *
     * @var string
     */
    private $paymentId;

    /**
     * This is the order ID of our project.
     *
     * @var string
     */
    private $reference;

    /** @var string */
    private $status;

    /** @var string|null */
    private $specificStatus;

    public function getPaymentId(): string
    {
        return $this->paymentId;
    }

    public function setPaymentId(string $paymentId): void
    {
        $this->paymentId = $paymentId;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getSpecificStatus(): ?string
    {
        return $this->specificStatus;
    }

    public function setSpecificStatus(?string $specificStatus): void
    {
        $this->specificStatus = $specificStatus;
    }

    public function signatureMatch(?string $signatureReceived): bool
    {
        $clientId = config('santander_payment.client_id');
        $secret = config('santander_payment.client_secret');
        $signatureExpected = hash_hmac('sha256', $clientId . $this->getPaymentId(), $secret);

        return $signatureExpected === $signatureReceived;
    }

    public function isReadyToShip(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }
}
