<?php

namespace Organisation\Form;

use DvsaClient\ViewModel\AbstractFormModel;
use Zend\Stdlib\Parameters;

class AeLinkSiteForm extends AbstractFormModel
{
    const FIELD_SITE_NR = 'siteNumber';

    /**
     * @var int
     */
    private $siteNumber;
    /**
     * @var string[]
     */
    private $sites;
    /**
     * @var int
     */
    private $maxInputLength;

    public function fromPost(Parameters $data)
    {
        $this->siteNumber = $data->get(self::FIELD_SITE_NR);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMaxInputLength()
    {
        return $this->maxInputLength;
    }

    /**
     * @param mixed $maxInputLength
     */
    public function setMaxInputLength($maxInputLength)
    {
        $this->maxInputLength = $maxInputLength;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @param mixed $siteId
     *
     * @return $this
     */
    public function setSiteNumber($siteId)
    {
        $this->siteNumber = $siteId;

        return $this;
    }

    /**
     * @return string[]
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @param mixed $typeAheadData
     *
     * @return $this
     */
    public function setSites($typeAheadData)
    {
        $this->sites = $typeAheadData;

        return $this;
    }
}
