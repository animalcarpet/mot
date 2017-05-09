<?php

namespace DvsaClient\Entity;

/**
 * Class Address.
 */
class Address
{
    private $addressLine1;
    private $addressLine2;
    private $addressLine3;
    private $postcode;
    private $county;
    private $town;
    private $country;

    /**
     * @param string $addressLine1
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine2
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine3
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setAddressLine3($addressLine3)
    {
        $this->addressLine3 = $addressLine3;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getAddressLine3()
    {
        return $this->addressLine3;
    }

    /**
     * @param string $postcode
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @param string $town
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * @param string $country
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $county
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getCounty()
    {
        return $this->county;
    }
}
