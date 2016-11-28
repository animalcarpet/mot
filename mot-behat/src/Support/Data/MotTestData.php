<?php
namespace Dvsa\Mot\Behat\Support\Data;

use Dvsa\Mot\Behat\Support\Api\ContingencyTest;
use Dvsa\Mot\Behat\Support\Api\MotTest;
use Dvsa\Mot\Behat\Support\Api\MysteryShopperTest;
use Dvsa\Mot\Behat\Support\Api\Session\AuthenticatedUser;
use Dvsa\Mot\Behat\Support\Data\Collection\SharedDataCollection;
use Dvsa\Mot\Behat\Support\Data\Params\MotTestParams;
use Dvsa\Mot\Behat\Support\Helper\TestSupportHelper;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use Zend\Http\Response as HttpResponse;

class MotTestData extends AbstractMotTestData
{
    private $contingencyData;
    private $contingencyMotTestData;
    private $demoMotTestData;
    private $normalMotTestData;
    private $mysteryShopperMotTestData;
    private $contingencyTest;
    private $mysteryShopperTest;

    const TEST_WITH_PRS = "prs";
    const TEST_WITH_ADVISORY = 'advisory';
    const TEST_TYPE_CONTINGENCY = "contingency";

    public function __construct(
        UserData $userData,
        ContingencyData $contingencyData,
        ContingencyMotTestData $contingencyMotTestData,
        DemoMotTestData $demoMotTestData,
        NormalMotTestData $normalMotTestData,
        MysteryShopperMotTestData $mysteryShopperMotTestData,
        ContingencyTest $contingencyTest,
        MotTest $motTest,
        MysteryShopperTest $mysteryShopperTest,
        BrakeTestResultData $brakeTestResultData,
        OdometerReadingData $odometerReadingData,
        ReasonForRejectionData $reasonForRejectionData,
        TestSupportHelper $testSupportHelper
    )
    {
        parent::__construct($userData, $motTest, $brakeTestResultData, $odometerReadingData, $reasonForRejectionData, $testSupportHelper);

        $this->contingencyData = $contingencyData;
        $this->contingencyMotTestData = $contingencyMotTestData;
        $this->demoMotTestData = $demoMotTestData;
        $this->normalMotTestData = $normalMotTestData;
        $this->mysteryShopperMotTestData = $mysteryShopperMotTestData;
        $this->contingencyTest = $contingencyTest;
        $this->mysteryShopperTest = $mysteryShopperTest;
        $this->motCollection = SharedDataCollection::get(MotTestDto::class);
    }

    public function create(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site = null,
                           $type = MotTestTypeCode::NORMAL_TEST)
    {
        switch ($type) {
            case MotTestTypeCode::NORMAL_TEST:
                return $this->normalMotTestData->create($tester, $vehicle, $site);
                break;
            case self::TEST_TYPE_CONTINGENCY:
                return $this->contingencyMotTestData->create($tester, $vehicle, $site);
                break;
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                return $this->demoMotTestData->create($tester, $vehicle);
                break;
            case MotTestTypeCode::MYSTERY_SHOPPER:
                return $this->mysteryShopperMotTestData->create($tester, $vehicle, $site);
                break;
            default:
                return $this->createWithType($tester, $vehicle, $site, $type);
        }
    }

    private function createWithType(AuthenticatedUser $tester, VehicleDto $vehicle, SiteDto $site, $type)
    {
       if (MotTestTypeCode::exists($type) === false) {
           throw new \InvalidArgumentException(sprintf("Unrecognised type '%s'", $type));
       }

        $response = $this
            ->motTest
            ->startMOTTest(
                $tester->getAccessToken(),
                $vehicle->getId(),
                $site->getId(),
                $vehicle->getVehicleClass()->getCode(),
                [MotTestParams::MOT_TEST_TYPE => $type]
            );

        $dto = $this->mapToMotTestDto(
            $tester,
            $vehicle,
            $response->getBody()->getData()[MotTestParams::MOT_TEST_NUMBER],
            $type,
            $site
        );

        $this->motCollection->add($dto, $dto->getMotTestNumber());

        return $dto;
    }

    public function createCompletedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, array $params)
    {
        $type = ArrayUtils::tryGet($params, MotTestParams::TYPE, MotTestTypeCode::NORMAL_TEST);
        $status = ArrayUtils::tryGet($params, MotTestParams::STATUS, MotTestStatusCode::PASSED);
        $rfrId = ArrayUtils::tryGet($params, MotTestParams::RFR_ID);

        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->finishMotTest($mot, $status, $rfrId);
    }

    public function finishMotTest(MotTestDto $mot, $status, $rfrId = null)
    {
        switch ($status) {
            case MotTestStatusCode::PASSED:
                return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
            case MotTestStatusCode::FAILED:
                return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot, $rfrId);
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

    /**
     * @return MotTestDto
     */
    public function getLast()
    {
        return $this->getAll()->last();
    }

    public function createPassedMotTest(AuthenticatedUser $tester, SiteDto $site = null, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->passMotTestWithDefaultBrakeTestAndMeterReading($mot);
    }

    public function createFailedMotTest(AuthenticatedUser $tester, SiteDto $site, VehicleDto $vehicle, $type = MotTestTypeCode::NORMAL_TEST, $rfrId = null)
    {
        $mot = $this->create($tester, $vehicle, $site, $type);
        return $this->failMotTestWithDefaultBrakeTestAndMeterReading($mot, $rfrId);
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

    public function getNormalMotTestLastResponse()
    {
        return $this->normalMotTestData->getLastResponse();
    }

    public function getDemoMotTestLastResponse()
    {
        return $this->demoMotTestData->getLastResponse();
    }

    public function getContingencyMotTestLastResponse()
    {
        return $this->contingencyMotTestData->getLastResponse();
    }

    public function getOtherMotTestLastResponse()
    {
        return $this->motTest->getLastResponse();
    }

    public function getLastResponse()
    {
        return $this->normalMotTestData->getLastResponse();
    }
}
