<?php

namespace DvsaCommon\Dto\Contact;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;
use DvsaCommon\Utility\AddressUtils;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use DvsaEntities\Entity\Address;

/**
 * Dto for address data
 */
class AddressDto extends AbstractDataTransferObject implements ReflectiveDtoInterface
{
    /** @var string */
    private $addressLine1;
    /** @var string */
    private $addressLine2;
    /** @var string */
    private $addressLine3;
    /** @var string */
    private $addressLine4;
    /** @var string */
    private $postcode;
    /** @var string */
    private $town;
    /** @var string */
    private $country;

    /**
     * @param array $data
     *
     * @return AddressDto
     */
    public static function fromArray($data)
    {
        TypeCheck::assertArray($data);

        $address = new self();
        $address
            ->setTown(ArrayUtils::tryGet($data, 'town'))
            ->setPostcode(ArrayUtils::tryGet($data, 'postcode'))
            ->setAddressLine1(ArrayUtils::tryGet($data, 'addressLine1'))
            ->setAddressLine2(ArrayUtils::tryGet($data, 'addressLine2'))
            ->setAddressLine3(ArrayUtils::tryGet($data, 'addressLine3'))
            ->setAddressLine4(ArrayUtils::tryGet($data, 'addressLine4'))
            ->setCountry(ArrayUtils::tryGet($data, 'country'));

        return $address;
    }

    public function toArray()
    {
        return [
            'addressLine1' => $this->getAddressLine1(),
            'addressLine2' => $this->getAddressLine2(),
            'addressLine3' => $this->getAddressLine3(),
            'addressLine4' => $this->getAddressLine4(),
            'town'         => $this->getTown(),
            'country'      => $this->getCountry(),
            'postcode'     => $this->getPostcode(),
        ];
    }

    /**
     * @param string $addressLine1
     *
     * @return $this
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine2
     * @return $this
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine3
     * @return $this
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @return string
     */
    public function getAddressLine4()
    {
        return $this->addressLine4;
    }

    /**
     * @param string $addressLine4
     * @return $this
     */
    public function setAddressLine4($addressLine4)
    {
        $this->addressLine4 = $addressLine4;

        return $this;
    }

    /**
     * @param string $postcode
     * @return $this
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $town
     * @return $this
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    public function getFullAddressString($sprtr = ', ')
    {
        return AddressUtils::stringify($this->toArray(), $sprtr);
    }

    public static function isEquals($dtoA, $dtoB)
    {
        return (
            ($dtoA === null && $dtoB === null)
            || (
                $dtoA instanceof AddressDto
                && $dtoB instanceof AddressDto
                && $dtoB->getAddressLine1() == $dtoA->getAddressLine1()
                && $dtoB->getAddressLine2() == $dtoA->getAddressLine2()
                && $dtoB->getAddressLine3() == $dtoA->getAddressLine3()
                && $dtoB->getAddressLine4() == $dtoA->getAddressLine4()
                && $dtoB->getTown() == $dtoA->getTown()
                && $dtoB->getCountry() == $dtoA->getCountry()
                && $dtoB->getPostcode() == $dtoA->getPostcode()
            )
        );
    }

    public function isEmpty()
    {
        return self::isEquals($this, new AddressDto());
    }
}
