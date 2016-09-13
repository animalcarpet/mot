<?php

namespace DvsaCommon\Dto\Vehicle;

use DateTime;
use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class VehicleExpiryDto implements ReflectiveDtoInterface
{
    /** @var  DateTime */
    private $earliestTestDateForPostdatingExpiryDate;
    /** @var  DateTime */
    private $expiryDate;
    private $isEarlierThanTestDateLimit;
    private $previousCertificateExists;

    /**
     * @return DateTime
     */
    public function getEarliestTestDateForPostdatingExpiryDate()
    {
        return $this->earliestTestDateForPostdatingExpiryDate;
    }

    /**
     * @param DateTime $earliestTestDateForPostdatingExpiryDate
     * @return VehicleExpiryDto
     */
    public function setEarliestTestDateForPostdatingExpiryDate(DateTime $earliestTestDateForPostdatingExpiryDate)
    {
        $this->earliestTestDateForPostdatingExpiryDate = $earliestTestDateForPostdatingExpiryDate;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getExpiryDate()
    {
        return $this->expiryDate;
    }

    /**
     * @param DateTime $expiryDate
     * @return VehicleExpiryDto
     */
    public function setExpiryDate(DateTime $expiryDate)
    {
        $this->expiryDate = $expiryDate;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsEarlierThanTestDateLimit()
    {
        return $this->isEarlierThanTestDateLimit;
    }

    /**
     * @param bool $isEarlierThanTestDateLimit
     * @return VehicleExpiryDto
     */
    public function setIsEarlierThanTestDateLimit($isEarlierThanTestDateLimit)
    {
        $this->isEarlierThanTestDateLimit = $isEarlierThanTestDateLimit;
        return $this;
    }

    /**
     * @return bool
     */
    public function getPreviousCertificateExists()
    {
        return $this->previousCertificateExists;
    }

    /**
     * @param bool $previousCertificateExists
     * @return VehicleExpiryDto
     */
    public function setPreviousCertificateExists($previousCertificateExists)
    {
        $this->previousCertificateExists = $previousCertificateExists;
        return $this;
    }

}