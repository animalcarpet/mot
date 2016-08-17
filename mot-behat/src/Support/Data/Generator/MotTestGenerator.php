<?php
namespace Dvsa\Mot\Behat\Support\Data\Generator;

use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\MotTestData;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;

class MotTestGenerator
{
    private $paramKeys = [
        "startedDate",
        "duration",
        "status",
        "rfrId"
    ];

    private $motTestData;

    private $duration;
    private $startedDate;
    private $rfrId;

    public function __construct(MotTestData $motTestData)
    {
        $this->motTestData = $motTestData;
    }

    /**
     * @return int
     */
    public function getDuration()
    {
        return $this->duration;
    }

    /**
     * @param int $duration
     * @return MotTestGenerator
     */
    public function setDuration($duration)
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStartedDate()
    {
        return $this->startedDate;
    }

    /**
     * @param mixed $startedDate
     * @return MotTestGenerator
     */
    public function setStartedDate($startedDate)
    {
        $this->startedDate = $startedDate;
        return $this;
    }

    /**
     * @return int
     */
    public function getRfrId()
    {
        return $this->rfrId;
    }

    /**
     * @param int $rfrId
     * @return MotTestGenerator
     */
    public function setRfrId($rfrId)
    {
        $this->rfrId = $rfrId;
        return $this;
    }



    public function generatePassedMotTests(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle)
    {
        return $this->generate($tester, $site, $vehicle, MotTestStatusCode::PASSED);
    }

    public function generatePassedMotTestsWithPrs(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle)
    {
        return $this->generate($tester, $site, $vehicle, "prs");
    }

    public function generateFailedMotTests(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle)
    {
        return $this->generate($tester, $site, $vehicle, MotTestStatusCode::FAILED);
    }

    public function generateAbandonedMotTests(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle)
    {
        return $this->generate($tester, $site, $vehicle, MotTestStatusCode::ABANDONED);
    }

    public function generateAbortedMotTests(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle)
    {
        return $this->generate($tester, $site, $vehicle, MotTestStatusCode::ABORTED);
    }

    public function generateMotTests(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle)
    {
        $this->generatePassedMotTests($tester, $site, $vehicle);
        $this->generatePassedMotTestsWithPrs($tester, $site, $vehicle);
        $this->generateFailedMotTests($tester, $site, $vehicle);
        $this->generateAbandonedMotTests($tester, $site, $vehicle);
    }

    private function generate(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $status)
    {
        $types = [MotTestTypeCode::NORMAL_TEST, MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING, "contingency"];

        $params = array_combine(
            $this->paramKeys,
            [
                $this->getStartedDate(),
                $this->getDuration(),
                $status,
                $this->getRfrId()
            ]
        );

        foreach ($types as $type) {
            if ($type === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING && $params["status"] === MotTestStatusCode::ABANDONED) {
                continue;
            }

            $params["type"] = $type;
            $this->motTestData->createCompletedTestInThePast($tester, $site, $vehicle, $params);
        }

        return $this->motTestData->getAll();
    }
}
