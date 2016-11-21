<?php

namespace DvsaMotTest\ViewModel\MotTestCertificate;

use Core\ViewModel\Badge\Badge;
use DvsaCommon\Constants\DuplicateCertificateSearchType;
use DvsaCommon\Dto\Vehicle\History\VehicleHistoryItemDto;

class MotTestCertificateTableItem
{
    private $dateOfTest;
    /** @var Badge $statusBadge */
    private $statusBadge;
    private $status;
    private $siteName;
    private $siteAddress;
    private $testNumber;
    private $paramsForSearchBy;

    /**
     * @return string
     */
    public function getDateOfTest()
    {
        return $this->dateOfTest;
    }

    /**
     * @param string $dateOfTest
     */
    public function setDateOfTest($dateOfTest)
    {
        $this->dateOfTest = $dateOfTest;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $testStatus
     */
    public function setTestStatus($testStatus)
    {
        $this->status = $testStatus;
        switch ($testStatus) {
           case VehicleHistoryItemDto::DISPLAY_PASS_STATUS_VALUE:
               $this->setStatusBadge(Badge::success());
               break;
            case VehicleHistoryItemDto::DISPLAY_FAIL_STATUS_VALUE:
                $this->setStatusBadge(Badge::alert());
                break;
            case VehicleHistoryItemDto::DISPLAY_ABAN_STATUS_VALUE:
                $this->setStatusBadge(Badge::normal());
                break;
        }
    }

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param string $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    /**
     * @return string
     */
    public function getSiteAddress()
    {
        return $this->siteAddress;
    }

    /**
     * @param string $siteAddress
     */
    public function setSiteAddress($siteAddress)
    {
        $this->siteAddress = $siteAddress;
    }

    /**
     * @return string
     */
    public function getTestNumber()
    {
        return $this->testNumber;
    }

    /**
     * @param string $testNumber
     */
    public function setTestNumber($testNumber)
    {
        $this->testNumber = $testNumber;
    }

    public function getStatusBadge()
    {
        return $this->statusBadge;
    }

    public function setStatusBadge($statusBadge)
    {
        $this->statusBadge = $statusBadge;
    }

    /**
     * @return array
     */
    public function getParamsForSearchBy()
    {
        return $this->paramsForSearchBy;
    }

    /**
     * @param array $paramsForSearchBy
     */
    public function setParamsForSearchBy($paramsForSearchBy)
    {
        $this->paramsForSearchBy = $paramsForSearchBy;
    }
}