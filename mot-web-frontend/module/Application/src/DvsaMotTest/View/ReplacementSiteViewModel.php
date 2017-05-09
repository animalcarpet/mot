<?php

namespace DvsaMotTest\View;

use DvsaCommon\Utility\AddressUtils;
use DvsaCommon\Utility\ArrayUtils;

class ReplacementSiteViewModel
{
    private $name;
    private $postcode;
    private $siteNumber;

    public function __construct(array $data)
    {
        $vts = ArrayUtils::tryGet($data, 'vts', []);
        $address = ArrayUtils::tryGet($vts, 'address', []);

        $this->name = ArrayUtils::tryGet($vts, 'name');
        $this->siteNumber = ArrayUtils::tryGet($vts, 'siteNumber');
        $this->postcode = ArrayUtils::tryGet($address, 'postcode');
        $this->siteLabel = $this->siteNumber.', '.AddressUtils::stringify($address);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->siteLabel;
    }
}
