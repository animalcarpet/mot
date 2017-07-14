<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\TestQualityInformation;

use Core\Formatting\FailedTestsPercentageFormatter;
use Core\Formatting\VehicleAgeFormatter;
use DvsaCommon\Date\TimeSpan;
use DvsaCommon\Dto\Contact\AddressDto;

class SiteRowViewModel
{
    private $siteId;
    private $siteName;
    /** @var AddressDto */
    private $siteAddress;
    private $testsDone;

    /** @var bool */
    private $isAverageVehicleAgeAvailable;
    private $averageVehicleAge;
    private $averageTestTime;
    private $testsFailedPercentage;
    private $tqiComponentsAtSiteUrl;

    public function __construct(
        $siteId,
        $siteName,
        $siteAddress,
        $testsDone,
        $isAverageVehicleAgeAvailable,
        $averageVehicleAge,
        TimeSpan $averageTestTime,
        $testsFailedPercentage,
        $tqiComponentsAtSiteUrl
    ) {
        $this->siteId = $siteId;
        $this->siteName = $siteName;
        $this->siteAddress = $siteAddress;
        $this->testsDone = $testsDone;
        $this->isAverageVehicleAgeAvailable = $isAverageVehicleAgeAvailable;
        $this->averageVehicleAge = $averageVehicleAge;
        $this->averageTestTime = $averageTestTime;
        $this->testsFailedPercentage = $testsFailedPercentage;
        $this->tqiComponentsAtSiteUrl = $tqiComponentsAtSiteUrl;
    }

    public function getSiteId()
    {
        return $this->siteId;
    }

    public function getSiteName()
    {
        return $this->siteName;
    }

    public function getSiteAddress()
    {
        return $this->siteAddress;
    }

    public function getTestsDone()
    {
        return $this->testsDone;
    }

    public function getAverageVehicleAgeAsString()
    {
        $vehicleAgeFormatter = new VehicleAgeFormatter();

        return $vehicleAgeFormatter->formatVehicleAge($this->averageVehicleAge, $this->isAverageVehicleAgeAvailable);
    }

    public function getAverageTestTime()
    {
        return $this->averageTestTime;
    }

    public function getTestsFailedPercentage()
    {
        $percentageFormatter = new FailedTestsPercentageFormatter();

        return $percentageFormatter->format($this->testsFailedPercentage);
    }

    public function getTqiComponentsAtSiteUrl()
    {
        return $this->tqiComponentsAtSiteUrl;
    }
}
