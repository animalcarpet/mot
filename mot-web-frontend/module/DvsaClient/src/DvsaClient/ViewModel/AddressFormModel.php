<?php

namespace DvsaClient\ViewModel;

use DvsaCommon\Dto\Contact\AddressDto;
use Zend\Stdlib\Parameters;

class AddressFormModel extends AbstractFormModel
{
    const FIELD_CONTACT = '%s[%s]';

    const FIELD_LINE1 = 'addressLine1';
    const FIELD_LINE2 = 'addressLine2';
    const FIELD_LINE3 = 'addressLine3';
    const FIELD_TOWN = 'addressTown';
    const FIELD_POSTCODE = 'addressPostCode';
    const FIELD_COUNTRY = 'addressCountry';

    const ERR_ADDRESS_REQUIRE = 'Address can\'t be empty';
    const ERR_TOWN_REQUIRE = 'Town can\'t be empty';
    const ERR_POSTCODE_REQUIRE = 'Post code can\'t be empty';

    private $addressLine1;
    private $addressLine2;
    private $addressLine3;
    private $town;
    private $postCode;
    private $country;

    public function fromPost(Parameters $postData)
    {
        $this
            ->setAddressLine1($postData->get(self::FIELD_LINE1))
            ->setAddressLine2($postData->get(self::FIELD_LINE2))
            ->setAddressLine3($postData->get(self::FIELD_LINE3))
            ->setPostCode($postData->get(self::FIELD_POSTCODE))
            ->setTown($postData->get(self::FIELD_TOWN))
            ->setCountry($postData->get(self::FIELD_COUNTRY));

        return $this;
    }

    /**
     * @return AddressDto
     */
    public function toDto()
    {
        return (new AddressDto())
            ->setAddressLine1($this->addressLine1)
            ->setAddressLine2($this->addressLine2)
            ->setAddressLine3($this->addressLine3)
            ->setTown($this->town)
            ->setPostcode($this->postCode)
            ->setCountry($this->country);
    }

    public function fromDto(AddressDto $address = null)
    {
        if ($address instanceof AddressDto) {
            $this
                ->setAddressLine1($address->getAddressLine1())
                ->setAddressLine2($address->getAddressLine2())
                ->setAddressLine3($address->getAddressLine3())
                ->setTown($address->getTown())
                ->setPostCode($address->getPostcode())
                ->setCountry($address->getCountry());
        }

        return $this;
    }

    public function isValid($type = null)
    {
        $this->resetErrors();
        $fieldAddress = $type ? sprintf(self::FIELD_CONTACT, $type, self::FIELD_LINE1) : self::FIELD_LINE1;
        $fieldTown = $type ? sprintf(self::FIELD_CONTACT, $type, self::FIELD_TOWN) : self::FIELD_TOWN;
        $fieldPostcode = $type ? sprintf(self::FIELD_CONTACT, $type, self::FIELD_POSTCODE) : self::FIELD_POSTCODE;

        if (empty($this->getAddressLine1())
            && empty($this->getAddressLine2())
            && empty($this->getAddressLine3())
        ) {
            $this->addError($fieldAddress, self::ERR_ADDRESS_REQUIRE);
            $this->addError(self::FIELD_LINE2);
            $this->addError(self::FIELD_LINE3);
        }

        if (empty($this->getTown())) {
            $this->addError($fieldTown, self::ERR_TOWN_REQUIRE);
        }

        if (empty($this->getPostCode())) {
            $this->addError($fieldPostcode, self::ERR_POSTCODE_REQUIRE);
        }

        return !$this->hasErrors();
    }

    public function isEmpty()
    {
        return empty($this->getAddressLine1())
            && empty($this->getAddressLine2())
            && empty($this->getAddressLine3())
            && empty($this->getTown())
            && empty($this->getPostCode());
    }

    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @return $this
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = trim($addressLine1);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @return $this
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = trim($addressLine2);

        return $this;
    }

    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @return $this
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = trim($addressLine3);

        return $this;
    }

    public function getTown()
    {
        return $this->town;
    }

    /**
     * @return $this
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * @return $this
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }
}
