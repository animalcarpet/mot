<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\AddressInputFilter;

class AddressStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = "ADDRESS";

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
            AddressInputFilter::FIELD_ADDRESS_1    => $this->getAddress1(),
            AddressInputFilter::FIELD_ADDRESS_2    => $this->getAddress2(),
            AddressInputFilter::FIELD_ADDRESS_3    => $this->getAddress3(),
            AddressInputFilter::FIELD_TOWN_OR_CITY => $this->getTownOrCity(),
            AddressInputFilter::FIELD_POSTCODE     => $this->getPostcode(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [
            AddressInputFilter::FIELD_ADDRESS_1,
            AddressInputFilter::FIELD_ADDRESS_2,
            AddressInputFilter::FIELD_ADDRESS_3,
            AddressInputFilter::FIELD_TOWN_OR_CITY,
            AddressInputFilter::FIELD_POSTCODE,
        ];
    }

    /**
     * describes the steps progress in the registration process.
     *
     * Step 1 of 6
     * Step 2 of 6
     * etc
     *
     * @return string|null
     */
    public function getProgress()
    {
        return "Step 2 of 6";
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        if (is_array($values) && count($values)) {
            $this->setAddress1($values[AddressInputFilter::FIELD_ADDRESS_1]);
            $this->setAddress2($values[AddressInputFilter::FIELD_ADDRESS_2]);
            $this->setAddress3($values[AddressInputFilter::FIELD_ADDRESS_3]);
            $this->setTownOrCity($values[AddressInputFilter::FIELD_TOWN_OR_CITY]);
            $this->setPostcode($values[AddressInputFilter::FIELD_POSTCODE]);
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
}