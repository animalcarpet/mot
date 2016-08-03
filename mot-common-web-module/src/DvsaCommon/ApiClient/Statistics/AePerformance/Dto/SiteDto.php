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
    /** @var  float */
    protected $riskAssessmentScore;
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
     * @return float
     */
    public function getRiskAssessmentScore()
    {
        return $this->riskAssessmentScore;
    }

    /**
     * @param float $riskAssessmentScore
     * @return SiteDto
     */
    public function setRiskAssessmentScore($riskAssessmentScore)
    {
        $this->riskAssessmentScore = $riskAssessmentScore;
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
