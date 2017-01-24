<?php

namespace TestSupport\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\ReasonForCancelId;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\HttpRestJson\Client as JsonClient;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\UrlBuilder\VehicleUrlBuilder;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\DtoHydrator;
use TestSupport\FieldValidation;
use TestSupport\Helper\RestClientGetterTrait;
use TestSupport\Helper\TestDataResponseHelper;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;

/**
 * Creates Mot tests for use by tests.
 *
 * Should not be deployed in production.
 */
class TestSupportMotTestController extends BaseTestSupportRestfulController
{
    use RestClientGetterTrait;

    const OTP = "123456";
    private static $RFR_ID_MAP = ["1" => 12, "2" => 12, "3" => 622, "4" => 622, "5" => 622, "7" => 622];

    private static $END_DATED_RFR_ID_MAP = ["1" => 811, "2" => 811, "3" => 726, "4" => 8827, "5" => 2537, "7" => 8801];

    public function create($data)
    {
        $this->validateData($data);

        $vehicleId = $data['vehicleId'];

        $restClient = $this->getRestClientService($data);

        $vehicle = $this->getVehicle($restClient, $vehicleId);
        $motTestNumber = $this->createMotTest($restClient, $vehicle, $data);

        $message = "Mot test created";
        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get(EntityManager::class);

        // retest requested
        if (isset($data['retest'])) {
            $retestId = $this->createRetest($restClient, $vehicle, $data);
            $retestIssueDate = DateUtils::toDate($data['retest']['issueDate']);
            $retestStartDate = DateUtils::toDateTime($data['retest']['startDate']);
            $retestCompletedDate = DateUtils::toDateTime($data['retest']['completedDate']);
            $retestExpiryDate = DateUtils::toDate($data['retest']['expiryDate']);
            $this->setIssuedDate(
                $entityManager,
                $retestId,
                $retestIssueDate,
                $retestStartDate,
                $retestCompletedDate,
                $retestExpiryDate
            );
            $message = "Mot test with retest created";
        }
        $motTestIssueDate = DateUtils::toDate($data['motTest']['issueDate']);
        $motTestStartDate = DateUtils::toDateTime($data['motTest']['startDate']);
        $motTestCompletedDate = DateUtils::toDateTime($data['motTest']['completedDate']);
        $motTestExpiryDate = DateUtils::toDate($data['motTest']['expiryDate']);
        $this->setIssuedDate(
            $entityManager,
            $motTestNumber,
            $motTestIssueDate,
            $motTestStartDate,
            $motTestCompletedDate,
            $motTestExpiryDate
        );
        $entityManager->flush();

        return TestDataResponseHelper::jsonOk(
            ["message" => $message, "motTestNumber" => $motTestNumber]
        );
    }

    private function validateData($data)
    {
        // general fields
        FieldValidation::checkForRequiredFieldsInData(
            ['vehicleId', 'vtsId', 'motTest'],
            $data
        );

        // mot test
        FieldValidation::checkForRequiredFieldsInData(
            ['mileage', 'outcome', 'issueDate', 'startDate', 'completedDate', 'expiryDate'],
            $data['motTest']
        );

        if (isset($data['retest'])) {
            FieldValidation::checkForRequiredFieldsInData(
                ['mileage', 'issueDate', 'startDate', 'completedDate', 'expiryDate'],
                $data['retest']
            );
        }
    }

    private function createMotTest(JsonClient $restClient, VehicleDto $vehicle, $data)
    {
        $vtsId = $data['vtsId'];
        $motTest = $data['motTest'];
        $mileage = $motTest['mileage'];
        $outcome = $motTest['outcome'];
        $vehicleClass = $vehicle->getClassCode();
        $rfrs = ArrayUtils::tryGet($motTest, 'rfrs', []);
        switch ($outcome) {
            case ReasonForRejectionTypeName::PRS:
                $status = MotTestStatusName::PASSED;
                break;
            case 'AUTO':
                $status = $this->hasFailingRfrs($rfrs)? MotTestStatusName::FAILED : MotTestStatusName::PASSED;
                break;
            default:
                $status = $outcome;
        }

        $motTestData = [
            'motTestType'             => isset($motTest['testType']) ? $motTest['testType'] : null,
            'vehicleId'               => $vehicle->getId(),
            'vehicleTestingStationId' => $vtsId,
            'countryOfRegistration'   => $vehicle->getCountryOfRegistration()->getId(),
            'primaryColour'           => $vehicle->getColour()->getCode(),
            'secondaryColour'         => $vehicle->getColourSecondary()->getCode(),
            'fuelTypeId'              => $vehicle->getFuelType()->getCode(),
            'cylinderCapacity'        => $vehicle->getCylinderCapacity(),
            'vehicleClassCode'        => $vehicleClass,
            'hasRegistration'         => true
        ];

        // Linking tests: check to see if an original test Id has been supplied
        // so we can create dynamically linked records for re-inspections.
        if (isset($data['reinspection'])) {
            $originalId = trim(ArrayUtils::tryGet($data['reinspection'], 'originalTestNumber', ''));
            if (strlen($originalId) > 0) {
                $motTestData['motTestNumberOriginal'] = $originalId;
            }
        }

        // create mot test
        $response = $motTestNumber = $restClient->post(
            UrlBuilder::of()->motTest()->toString(),
            $motTestData
        );

        $motTestNumber = $response['data']['motTestNumber'];

        $this->setOdometerReading($restClient, $motTestNumber, $mileage);
        $this->setBrakeTestResult($restClient, $motTestNumber, $vehicleClass, $outcome);

        if (!is_null($rfrs) && count($rfrs) > 0) {
            foreach ($rfrs as $rfr) {
                $this->addRfrToDb($rfr, $motTestNumber);
            }
        } elseif ($outcome === 'PRS') {
            $this->addExemplaryRfr(
                $restClient,
                $motTestNumber,
                $vehicleClass,
                ReasonForRejectionTypeName::PRS
            );
        }

        if (MotTestStatusName::ACTIVE !== $status) {
            $this->setStatus($restClient, $motTestNumber, $status);
        }

        return $motTestNumber;
    }

    private function hasFailingRfrs($rfrs)
    {
        return ArrayUtils::anyMatch(
            $rfrs,
            function ($rfr) {
                return !ArrayUtils::hasNotEmptyValue($rfr, 'type')
                    || $rfr['type'] === ReasonForRejectionTypeName::FAIL;
            }
        );
    }

    private function createRetest(JsonClient $restClient, VehicleDto $vehicle, $data)
    {
        $vtsId = $data['vtsId'];
        $mileage = $data['retest']['mileage'];
        $outcome = $data['retest']['outcome'];
        $vehicleClass = $vehicle->getClassCode();
        $status = $outcome;

        $motTestData = [
            'vehicleId'               => $vehicle->getId(),
            'vehicleTestingStationId' => $vtsId,
            'primaryColour'           => $vehicle->getColour()->getCode(),
            'secondaryColour'         => $vehicle->getColourSecondary()->getCode(),
            'fuelTypeId'              => $vehicle->getFuelType()->getCode(),
            'vehicleClassCode'        => $vehicleClass,
            'oneTimePassword'         => self::OTP,
            'hasRegistration'         => true
        ];

        // create mot test
        $apiResult = $restClient->post(MotTestUrlBuilder::retest()->toString(), $motTestData);
        $motTestNumber = $apiResult['data']['motTestNumber'];

        $this->setOdometerReading($restClient, $motTestNumber, $mileage);
        $this->setBrakeTestResult($restClient, $motTestNumber, $vehicleClass, $status);
        $this->setStatus($restClient, $motTestNumber, $status);

        return $motTestNumber;
    }

    private function addExemplaryRfr(JsonClient $client, $motTestNumber, $vehicleClass, $type)
    {
        $rfrUrl = MotTestUrlBuilder::reasonForRejection($motTestNumber)->toString();
        $client->post($rfrUrl, ['rfrId' => self::$RFR_ID_MAP[$vehicleClass], 'type' => $type]);
    }

    /**
     * @param $comment
     * @return int|null
     */
    private function addRfrCommentToDb($id, $comment)
    {
        if (is_null($comment)) {
            $comment = 'alireza';
        }

        $sql = 'INSERT INTO `mot_test_rfr_map_comment` (`id`, `comment`) VALUES (:id, :comment)';
        $params = [
            'id' => $id,
            'comment' => $comment
        ];

        return $this->updateInDb($sql, $params);
    }

    private function addRfrToDb($rfr, $motTestNumber)
    {
        $sql = 'INSERT INTO mot_test_current_rfr_map (
          rfr_id,
          failure_dangerous,
          generated,
          on_original_test,
          created_on,
          created_by,
          mot_test_id,
          rfr_type_id
        ) values (
          :rfrId,
          :dangerous,
          :generated,
          0,
          current_date,
          (SELECT person_id FROM mot_test_current WHERE number = :motTestNumber),
          (SELECT id FROM mot_test_current WHERE number = :motTestNumber),
          (SELECT id FROM reason_for_rejection_type WHERE name = :type)
        )';

        $values = [
            'motTestNumber' => $motTestNumber,
            'rfrId' => ArrayUtils::tryGet($rfr, 'id'),
            'type' => ArrayUtils::tryGet($rfr, 'type', ReasonForRejectionTypeName::FAIL),
            'dangerous' => ArrayUtils::tryGet($rfr, 'dangerous', 0),
            'generated' => ArrayUtils::tryGet($rfr, 'generated', 0),
        ];

        if(empty($values['rfrId'])){
            $values['rfrId'] = ArrayUtils::get($rfr, 'reasonId');
        }

        $id = $this->updateInDb($sql, $values);

        $comment = ArrayUtils::tryGet($rfr, 'comment');

        $this->addRfrCommentToDb($id, $comment);
    }

    /**
     * @param string $query
     * @param array $bindValues
     * @return int
     */
    private function updateInDb($query, $bindValues = [])
    {
        $entityManager = $this->getServiceLocator()->get(EntityManager::class);
        $stmt = $entityManager->getConnection()->prepare($query);
        foreach ($bindValues as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $entityManager->getConnection()->lastInsertId();
    }

    /**
     * Issued Date cannot be changed normally, so it has to be hacked
     */
    private function setIssuedDate(
        EntityManager $em,
        $motTestNumber,
        \DateTime $issueDate,
        \DateTime $startedDate,
        \DateTime $completedDate,
        \DateTime $expiryDate
    ) {
        $stmt = $em->getConnection()->prepare(
            "UPDATE mot_test_current m LEFT JOIN mot_test_current n on n.id = m.prs_mot_test_id "
            . "SET m.started_date = ?, m.completed_date = ?, m.issued_date = ?, m.expiry_date = ? "
            . "WHERE m.number = ?"
        );

        $stmt->bindValue(1, $startedDate, 'datetime');
        $stmt->bindValue(2, $completedDate, 'datetime');
        $stmt->bindValue(3, $issueDate, 'date');
        $stmt->bindValue(4, $expiryDate, 'date');
        $stmt->bindValue(5, $motTestNumber);

        $stmt->execute();

        $stmt = $em->getConnection()->prepare(
            "UPDATE mot_test_current m LEFT JOIN mot_test_current n on n.id = m.prs_mot_test_id "
            . "SET m.started_date = ?, m.completed_date = ?, m.issued_date = ?, m.expiry_date = ? "
            . "WHERE n.number=?"
        );

        $stmt->bindValue(1, $startedDate, 'datetime');
        $stmt->bindValue(2, $completedDate, 'datetime');
        $stmt->bindValue(3, $issueDate, 'date');
        $stmt->bindValue(4, $expiryDate, 'date');
        $stmt->bindValue(5, $motTestNumber);

        $stmt->execute();

    }

    /**
     * @param JsonClient $client
     * @param            $vehicleId
     *
     * @return VehicleDto
     */
    private function getVehicle(JsonClient $client, $vehicleId)
    {
        $response = $client->get(VehicleUrlBuilder::vehicle($vehicleId)->toString());

        return (new DtoHydrator())->doHydration($response['data']);
    }

    private function setOdometerReading(JsonClient $client, $motTestNumber, $mileage)
    {
        $apiUrl = MotTestUrlBuilder::odometerReading($motTestNumber)->toString();

        // set odometer reading
        $client->putJson(
            $apiUrl,
            [
                'value'      => $mileage,
                'unit'       => OdometerUnit::MILES,
                'resultType' => OdometerReadingResultType::OK
            ]
        );
    }

    private function setBrakeTestResult(JsonClient $client, $motTestNumber, $vehicleClass, $outcome)
    {
        $isPassed = in_array($outcome, ['PASSED', 'ACTIVE', 'AUTO', 'PRS']);
        $class12 = in_array($vehicleClass, [VehicleClassCode::CLASS_1, VehicleClassCode::CLASS_2]);
        $brakeTestResultUrl = UrlBuilder::of()->motTest()->routeParam('motTestNumber', $motTestNumber)
            ->brakeTestResult()->toString();
        // set brake test PASS based on the class
        if ($class12) {
            $brakeTestResult = [
                'brakeTestType'           => BrakeTestTypeCode::DECELEROMETER,
                'control1BrakeEfficiency' => $isPassed ? 90 : 10,
                'control2BrakeEfficiency' => $isPassed ? 90 : 10,
                'isSideAttached'          => '0'
            ];
        } else {
            $brakeTestResult = [
                "serviceBrake1TestType"   => BrakeTestTypeCode::DECELEROMETER,
                "parkingBrakeTestType"    => BrakeTestTypeCode::DECELEROMETER,
                "serviceBrake1Efficiency" => $isPassed ? 80 : 10,
                "parkingBrakeEfficiency"  => $isPassed ? 80 : 10
            ];
        }

        $client->post($brakeTestResultUrl, $brakeTestResult);
    }

    private function setStatus(JsonClient $client, $motTestNumber, $status)
    {
        $apiUrl = MotTestUrlBuilder::motTestStatus($motTestNumber)->toString();

        $data = [
            'status'          => $status,
            'oneTimePassword' => self::OTP
        ];

        if ($status === MotTestStatusName::ABANDONED || $status === MotTestStatusName::ABORTED) {
            $data['reasonForCancelId'] = ReasonForCancelId::DANGR;
            $data['cancelComment'] = 'ABANDONED test from TEST SUPPORT HELPER';
        }

        $client->post($apiUrl, $data);
    }
}
