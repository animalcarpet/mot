<?php

namespace DvsaCommon\Dto\Vehicle;

use DvsaCommon\Dto\Common\AbstractStaticDataDto;

/**
 * Class CountryDto
 *
 * @package DvsaCommon\Dto\Vehicle
 */
class CountryDto extends AbstractStaticDataDto
{
    /** @var string */
    private $code;
    /** @var string */
    private $name;
    /** @var string */
    private $licensingCode;

    /**
     * @param string $code
     *
     * @return CountryDto
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $name
     *
     * @return CountryDto
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $licensingCode
     *
     * @return CountryDto
     */
    public function setLicensingCode($licensingCode)
    {
        $this->licensingCode = $licensingCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicensingCode()
    {
        return $this->licensingCode;
    }
}
