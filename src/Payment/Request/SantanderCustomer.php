<?php

namespace Payever\Santander\Payment\Request;

use Payever\Santander\Exceptions\SantanderValidationException;

class SantanderCustomer
{
    const COUNTRY = 'DK';

    /** @var string */
    private $firstName;

    /** @var string */
    private $lastName;

    /** @var string */
    private $street;

    /** @var string */
    private $city;

    /** @var string */
    private $country = self::COUNTRY;

    /** @var string */
    private $zip;

    /** @var string */
    private $phone;

    /** @var string */
    private $email;

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): void
    {
        $this->street = $street;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): void
    {
        $this->city = $city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getZip(): string
    {
        return $this->zip;
    }

    public function setZip(string $zip): void
    {
        $this->zip = $zip;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @throws SantanderValidationException
     */
    public function validate(): void
    {
        foreach ([
            'firstName',
            'lastName',
            'street',
            'city',
            'country',
            'zip',
            'phone',
            'email',
        ] as $field) {
            if (!$this->$field) {
                throw new SantanderValidationException('SantanderCustomer is not complete. Missing '.$field);
            }
        }
    }

    /**
     * @throws SantanderValidationException
     */
    public function getData(): array
    {
        $this->validate();

        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'street' => $this->getStreet(),
            'zip' => $this->getZip(),
            'city' => $this->getCity(),
            'country' => $this->getCountry(),
            'phone' => $this->getPhone(),
            'email' => $this->getEmail(),
        ];
    }
}
