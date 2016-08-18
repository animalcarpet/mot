<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\BrakeTestResult;
use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\DemoTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\OdometerReading;
use Dvsa\Mot\Behat\Support\Api\ReasonForRejection;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;

class MotTestData extends AbstractMotTestData
{
    private $contingencyData;
    private $contingencyMotTestData;
    private $demoMotTestData;
    private $normalMotTestData;
    private $contingencyTest;
    private $demoTest;
    private $testSupportHelper;
    protected $motCollection;

    const TEST_WITH_PRS = "prs";
    const TEST_WITH_ADVISORY = 'advisory';

    public function __construct(
        UserData $userData,
        ContingencyData $contingencyData,
        ContingencyMotTestData $contingencyMotTestData,
        DemoMotTestData $demoMotTestData,
        NormalMotTestData $normalMotTestData,
        ContingencyTest $contingencyTest,
        MotTest $motTest,
        DemoTest $demoTest,
        BrakeTestResult $brakeTestResult,
        OdometerReading $odometerReading,
        ReasonForRejection $reasonForRejection,
        TestSupportHelper $testSupportHelper
    )
    {
        parent::__construct($userData, $motTest, $brakeTestResult, $odometerReading, $reasonForRejection);

        $this->contingencyData = $contingencyData;
        $this->contingencyMotTestData = $contingencyMotTestData;
        $this->demoMotTestData = $demoMotTestData;
        $this->normalMotTestData = $normalMotTestData;
        $this->contingencyTest = $contingencyTest;
        $this->demoTest = $demoTest;
        $this->testSupportHelper = $testSupportHelper;
        $this->motCollection = SharedDataCollection::get(MotTestDto::class);
    }

    public function create(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site = null, $type = MotTestTypeCode::NORMAL_TEST)
    {
        switch ($type) {
            case MotTestTypeCode::NORMAL_TEST:
                return $this->normalMotTestData->create($tester, $vehicle, $site);
                break;
            case 'contingency':
                return $this->contingencyMotTestData->create($tester, $vehicle, $site);
                break;
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                return $this->demoMotTestData->create($tester, $vehicle);
                break;
            default:
                throw new \InvalidArgumentException(sprintf("Unrecognised type '%s'", $type));
        }
    }

    public function createCompletedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, array $params)
    {
        $type = ArrayUtils::tryGet($params, "type");
        $status = ArrayUtils::tryGet($params, "status");
        $rfrId = ArrayUtils::tryGet($params, "rfrId");

        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->finishMotTest($mot, $status, $rfrId);
    }

    public function finishMotTest(MotTestDto $mot, $status, $rfrId = null)
    {
        switch ($status) {
            case MotTestStatusCode::PASSED:
                return $this->passMotTest($mot);
            case MotTestStatusCode::FAILED:
                return $this->failMotTest($mot, $rfrId);
            case self::TEST_WITH_PRS:
                return $this->failMotTestWithPrs($mot, $rfrId);
            case self::TEST_WITH_ADVISORY:
                return $this->failMotTestWithAdvisory($mot, $rfrId);
            case MotTestStatusCode::ABANDONED:
                return $this->abandonMotTest($mot);
            case MotTestStatusCode::ABORTED:
                return $this->abandonMotTest($mot);
            default:
                throw new \InvalidArgumentException(sprintf("Unrecognised status '%s'", "status"));
        }
    }

    public function createCompletedTestInThePast(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, array $params)
    {
        $startedDate = ArrayUtils::tryGet($params, "startedDate");
        $duration = ArrayUtils::tryGet($params, "duration");

        if ($startedDate === null || $duration === null) {
            throw new \InvalidArgumentException("Missing 'startedDate' or 'duration' param");
        }

        unset($params["startedDate"]);
        unset($params["duration"]);

        if (!$startedDate instanceof \DateTime) {
            $startedDate = new \DateTime($startedDate);
        }

        $completedDate = clone $startedDate;
        $completedDate->add(new \DateInterval('PT' . $duration . 'M'));

        $mot = $this->createCompletedMotTest($tester, $site, $vehicle, $params);
        $this->testSupportHelper->getMotService()->changeDate($mot->getMotTestNumber(), $startedDate, $completedDate);

        $mot->setStartedDate($startedDate->format(DateTimeApiFormat::FORMAT_ISO_8601_UTC_TZ));
        $mot->setCompletedDate($completedDate->format(DateTimeApiFormat::FORMAT_ISO_8601_UTC_TZ));

        return $mot;
    }

    public function get($motTestNumber)
    {
        return $this->getAll()->get($motTestNumber);
    }

    public function getAll()
    {
        return $this->motCollection;
    }

    public function createPassedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->passMotTest($mot);
    }

    public function createFailedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST, $rfrId = null)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->failMotTest($mot, $rfrId);
    }

    public function createPassedMotTestWithPrs(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST, $rfrId)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->failMotTestWithPrs($mot, $rfrId);
    }

    public function createAbandonedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->abandonMotTest($mot);
    }

    public function createAbortedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->abortMotTest($mot);
    }
}
