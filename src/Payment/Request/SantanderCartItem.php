<?php

namespace Payever\Santander\Payment\Request;

use Payever\Santander\Exceptions\SantanderValidationException;

class SantanderCartItem
{
    /** @var string */
    private $name;

    /** @var float */
    private $price;

    /** @var string */
    private $thumbnail;

    /** @var int */
    private $quantity = 1;

    /** @var float|null */
    private $priceNetto = null;

    /** @var float|null */
    private $vatRate = null;

    /** @var string|null */
    private $description = null;

    /** @var string|null */
    private $sku = null;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getThumbnail(): string
    {
        return $this->thumbnail;
    }

    public function setThumbnail(string $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }

    public function getPriceNetto(): ?float
    {
        return $this->priceNetto;
    }

    public function setPriceNetto(?float $priceNetto): void
    {
        $this->priceNetto = $priceNetto;
    }

    public function getVatRate(): ?float
    {
        return $this->vatRate;
    }

    public function setVatRate(?float $vatRate): void
    {
        $this->vatRate = $vatRate;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSku(): ?string
    {
        return $this->sku;
    }

    public function setSku(?string $sku): void
    {
        $this->sku = $sku;
    }

    public function validate(): void
    {
        foreach ([
            'name',
            'price',
            'thumbnail',
        ] as $field) {
            if (!$this->$field) {
                throw new SantanderValidationException('SantanderCartItem is not complete. Missing '.$field);
            }
        }
        if ($this->quantity <1) {
            throw new SantanderValidationException('SantanderCartItem is invalid. Quantity should be minimum 1');
        }
    }

    /**
     * @throws SantanderValidationException
     */
    public function getData(): array
    {
        $this->validate();

        $result = [
            'name' => $this->getName(),
            'price' => $this->getPrice(),
            'thumbnail' => $this->getThumbnail(),
            'quantity' => $this->getQuantity(),
        ];
        if (($value = $this->getPriceNetto()) !== null) {
            $result['priceNetto'] = $value;
        }
        if (($value = $this->getVatRate()) !== null) {
            $result['vatRate'] = $value;
        }
        if (($value = $this->getDescription()) !== null) {
            $result['description'] = $value;
        }
        if (($value = $this->getSku()) !== null) {
            $result['sku'] = $value;
        }

        return $result;
    }
}
