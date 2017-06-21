<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\ContactDetailsInputFilter;

class ContactDetailsStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'CONTACT_DETAILS';

    /**
     * @var string
     */
    private $address1;

    /**
     * @var string
     */
    private $address2;

    /**
     * @var string
     */
    private $address3;

    /**
     * @var string
     */
    private $townOrCity;

    /**
     * @var string
     */
    private $postcode;

    /**
     * @var string
     */
    private $phone;

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return $this
     */
    public function load()
    {
        $values = $this->sessionService->load(self::STEP_ID);
        $this->readFromArray($values);

        return $this;
    }

    /**
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            ContactDetailsInputFilter::FIELD_ADDRESS_1 => $this->getAddress1(),
            ContactDetailsInputFilter::FIELD_ADDRESS_2 => $this->getAddress2(),
            ContactDetailsInputFilter::FIELD_ADDRESS_3 => $this->getAddress3(),
            ContactDetailsInputFilter::FIELD_TOWN_OR_CITY => $this->getTownOrCity(),
            ContactDetailsInputFilter::FIELD_POSTCODE => $this->getPostcode(),
            ContactDetailsInputFilter::FIELD_PHONE => $this->getPhone(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [
            ContactDetailsInputFilter::FIELD_ADDRESS_1,
            ContactDetailsInputFilter::FIELD_ADDRESS_2,
            ContactDetailsInputFilter::FIELD_ADDRESS_3,
            ContactDetailsInputFilter::FIELD_TOWN_OR_CITY,
            ContactDetailsInputFilter::FIELD_POSTCODE,
            ContactDetailsInputFilter::FIELD_PHONE,
        ];
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        if (is_array($values) && count($values)) {
            $this->setAddress1($values[ContactDetailsInputFilter::FIELD_ADDRESS_1]);
            $this->setAddress2($values[ContactDetailsInputFilter::FIELD_ADDRESS_2]);
            $this->setAddress3($values[ContactDetailsInputFilter::FIELD_ADDRESS_3]);
            $this->setTownOrCity($values[ContactDetailsInputFilter::FIELD_TOWN_OR_CITY]);
            $this->setPostcode(strtoupper($values[ContactDetailsInputFilter::FIELD_POSTCODE]));
            $this->setPhone($values[ContactDetailsInputFilter::FIELD_PHONE]);

            $this->filter->setData($this->toArray());
        }
    }

    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'account-register/address';
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
    }

    /**
     * @return string
     */
    public function getAddress3()
    {
        return $this->address3;
    }

    /**
     * @param string $address3
     */
    public function setAddress3($address3)
    {
        $this->address3 = $address3;
    }

    /**
     * @return string
     */
    public function getTownOrCity()
    {
        return $this->townOrCity;
    }

    /**
     * @param string $townOrCity
     */
    public function setTownOrCity($townOrCity)
    {
        $this->townOrCity = $townOrCity;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $postcode
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }
}
