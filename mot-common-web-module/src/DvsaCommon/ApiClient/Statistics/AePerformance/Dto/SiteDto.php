<?php

namespace DvsaCommon\ApiClient\Statistics\AePerformance\Dto;

use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class SiteDto implements ReflectiveDtoInterface
{
    /** @var  int */
    protected $id;
    /** @var  string */
    protected $name;
    /** @var  string */
    protected $number;
    /** @var  RiskAssessmentDto */
    protected $currentRiskAssessment;
    /** @var  RiskAssessmentDto */
    protected $previousRiskAssessment;
    /** @var  AddressDto */
    protected $address;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return SiteDto
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @param string $name
     * @return SiteDto
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return SiteDto
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return RiskAssessmentDto
     */
    public function getCurrentRiskAssessment()
    {
        return $this->currentRiskAssessment;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\AePerformance\Dto\RiskAssessmentDto $riskAssessment
     * @return SiteDto
     */
    public function setCurrentRiskAssessment(RiskAssessmentDto $riskAssessment = null)
    {
        $this->currentRiskAssessment = $riskAssessment;
        return $this;
    }

    /**
     * @return RiskAssessmentDto
     */
    public function getPreviousRiskAssessment()
    {
        return $this->previousRiskAssessment;
    }

    /**
     * @param \DvsaCommon\ApiClient\Statistics\AePerformance\Dto\RiskAssessmentDto $riskAssessment
     * @return SiteDto
     */
    public function setPreviousRiskAssessment(RiskAssessmentDto $riskAssessment = null)
    {
        $this->previousRiskAssessment = $riskAssessment;
        return $this;
    }

    /**
     * @return AddressDto
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param \DvsaCommon\Dto\Contact\AddressDto $address
     * @return SiteDto
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }
    
}
