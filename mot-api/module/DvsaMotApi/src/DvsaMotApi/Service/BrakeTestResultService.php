<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Constants\BrakeTestConfigurationClass1And2;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Domain\BrakeTestTypeConfiguration;
use DvsaCommon\Enum\BrakeTestTypeCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Mapper\BrakeTestWeightSourceMapper;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaEntities\Entity\BrakeTestResultClass12;
use DvsaEntities\Entity\BrakeTestResultClass3AndAbove;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionComment;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\WeightSourceRepository;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Mapper\BrakeTestResultClass12Mapper;
use DvsaMotApi\Mapper\BrakeTestResultClass3AndAboveMapper;
use DvsaMotApi\Mapper\ParkingBrakeClass3AndAboveRfrMapper;
use DvsaMotApi\Mapper\ServiceBrakeImbalanceSpecialProcessingRfrMapper;
use DvsaMotApi\Service\Calculator\BrakeImbalanceResult;
use DvsaMotApi\Service\Calculator\BrakeTestClass3AndAboveCalculationResult;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass1And2Calculator;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaMotApi\Service\Helper\BrakeTestResultsHelper;
use DvsaMotApi\Service\Helper\ExtractionHelper;
use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use DvsaMotApi\Service\Validator\BrakeTestResultValidator;
use DvsaMotApi\Service\Validator\MotTestValidator;

/**
 * Class BrakeTestResultService.
 */
class BrakeTestResultService extends AbstractService
{

    const RFR_ID_BRAKE_EFFICIENCY_ROLLER_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2 = '489';
    const RFR_ID_BRAKE_EFFICIENCY_ROLLER_ONE_BELOW_SECONDARY_MIN_CLASS_1_2 = '490';
    const RFR_ID_BRAKE_EFFICIENCY_ROLLER_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2 = '491';

    const RFR_ID_BRAKE_EFFICIENCY_PLATE_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2 = '499';
    const RFR_ID_BRAKE_EFFICIENCY_PLATE_ONE_BELOW_SECONDARY_MIN_CLASS_1_2 = '500';
    const RFR_ID_BRAKE_EFFICIENCY_PLATE_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2 = '501';

    const RFR_ID_BRAKE_EFFICIENCY_GRADIENT_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2 = '509';
    const RFR_ID_BRAKE_EFFICIENCY_GRADIENT_ONE_BELOW_SECONDARY_MIN_CLASS_1_2 = '510';
    const RFR_ID_BRAKE_EFFICIENCY_GRADIENT_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2 = '764';

    const RFR_ID_BRAKE_EFFICIENCY_FLOOR_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2 = '502';
    const RFR_ID_BRAKE_EFFICIENCY_FLOOR_ONE_BELOW_SECONDARY_MIN_CLASS_1_2 = '503';
    const RFR_ID_BRAKE_EFFICIENCY_FLOOR_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2 = '763';

    const RFR_ID_BRAKE_EFFICIENCY_DECELEROMETER_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2 = '861';
    const RFR_ID_BRAKE_EFFICIENCY_DECELEROMETER_ONE_BELOW_SECONDARY_MIN_CLASS_1_2 = '862';
    const RFR_ID_BRAKE_EFFICIENCY_DECELEROMETER_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2 = '863';

    const MAX_NUMBER_AXLES = 3;

    const PARKING_BRAKE_SINGLE_LINE_MAJOR_SEVERITY_THRESHOLD = '25';
    const PARKING_BRAKE_DUAL_LINE_MAJOR_SEVERITY_THRESHOLD = '16';
    const PARKING_BRAKE_DUAL_LINE_DANGEROUS_SEVERITY_THRESHOLD = '8';

    private $objectHydrator;
    private $brakeTestResultValidator;
    private $brakeTestConfigurationValidator;
    private $brakeTestResultCalculator;
    private $brakeTestResultCalculatorClass1And2;
    private $authService;
    private $brakeTestResultClass3AndAboveMapper;
    private $brakeTestResultClass12Mapper;
    private $motTestValidator;
    /** @var MotTestReasonForRejectionService */
    private $motTestReasonForRejectionService;
    private $performMotTestAssertion;
    private $weightSourceRepository;
    /** @var FeatureToggles */
    private $featureToggles;
    /** @var ParkingBrakeClass3AndAboveRfrMapper */
    private $parkingBrakeMapper;
    /** @var ServiceBrakeTestSpecialRfrGenerator  */
    private $serviceBrakeRfrGenerator;
    /** @var ServiceBrakeImbalanceSpecialProcessingRfrMapper */
    private $serviceBrakeImbalanceMapper;

    private $brakesWithLockApplicable =
        [
            BrakeTestTypeCode::ROLLER,
            BrakeTestTypeCode::PLATE,
        ];

    public function __construct(
        EntityManager $entityManager,
        BrakeTestResultValidator $brakeTestResultValidator,
        BrakeTestConfigurationValidator $brakeTestConfigurationValidator,
        DoctrineObject $objectHydrator,
        BrakeTestResultClass3AndAboveCalculator $brakeTestResultCalculator,
        BrakeTestResultClass1And2Calculator $brakeTestResultClass1And2Calculator,
        BrakeTestResultClass3AndAboveMapper $brakeTestResultClass3AndAboveMapper,
        BrakeTestResultClass12Mapper $brakeTestResultClass12Mapper,
        AuthorisationServiceInterface $authService,
        MotTestValidator $motTestValidator,
        MotTestReasonForRejectionService $motTestReasonForRejectionService,
        ApiPerformMotTestAssertion $performMotTestAssertion,
        WeightSourceRepository $weightSourceRepository,
        FeatureToggles $featureToggles
    ) {
        parent::__construct($entityManager);
        $this->brakeTestResultValidator = $brakeTestResultValidator;
        $this->brakeTestConfigurationValidator = $brakeTestConfigurationValidator;
        $this->objectHydrator = $objectHydrator;
        $this->brakeTestResultCalculator = $brakeTestResultCalculator;
        $this->brakeTestResultCalculatorClass1And2 = $brakeTestResultClass1And2Calculator;
        $this->brakeTestResultClass3AndAboveMapper = $brakeTestResultClass3AndAboveMapper;
        $this->brakeTestResultClass12Mapper = $brakeTestResultClass12Mapper;
        $this->authService = $authService;
        $this->motTestValidator = $motTestValidator;
        $this->motTestReasonForRejectionService = $motTestReasonForRejectionService;
        $this->performMotTestAssertion = $performMotTestAssertion;
        $this->weightSourceRepository = $weightSourceRepository;
        $this->featureToggles = $featureToggles;
        $this->serviceBrakeRfrGenerator = new ServiceBrakeTestSpecialRfrGenerator();
        $this->parkingBrakeMapper = new ParkingBrakeClass3AndAboveRfrMapper($featureToggles);
        $this->serviceBrakeImbalanceMapper = new ServiceBrakeImbalanceSpecialProcessingRfrMapper($featureToggles);
    }

    public function createBrakeTestResult(MotTest $motTest, $brakeTestResultData)
    {
        $this->performMotTestAssertion->assertGranted($motTest);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        $vehicle = $motTest->getVehicle();

        switch ($vehicle->getVehicleClass()->getCode()) {
            case Vehicle::VEHICLE_CLASS_1:
            case Vehicle::VEHICLE_CLASS_2:
                return $this->validateAndCalculateBrakeTestResultClass1And2(
                    $brakeTestResultData,
                    $motTest
                );
            default:
                return $this->validateAndCalculateBrakeTestResultClass3AndAbove(
                    $brakeTestResultData,
                    $motTest
                );
        }
    }

    public function updateBrakeTestResult(MotTest $motTest, $brakeTestResultData)
    {
        $this->performMotTestAssertion->assertGranted($motTest);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        $result = $this->createBrakeTestResult($motTest, $brakeTestResultData);
        if ($result->brakeTestResultClass1And2) {
            $motTest->setBrakeTestResultClass12($result->brakeTestResultClass1And2);
        }
        if ($result->brakeTestResultClass3AndAbove) {
            $motTest->setBrakeTestResultClass3AndAbove($result->brakeTestResultClass3AndAbove);
        }

        $rfrRepository = $this->entityManager->getRepository(MotTestReasonForRejection::class);
        $rfrsToDelete = $rfrRepository->findBy(['generated' => true, 'motTestId' => $motTest->getId()]);
        /** @var MotTestReasonForRejection $deletedRfr */
        foreach ($rfrsToDelete as $deletedRfr) {
            $this->motTestReasonForRejectionService->removeReasonForRejection($deletedRfr);
            $motTest->removeMotTestReasonForRejectionById($deletedRfr->getId());
        }

        foreach ($result->reasonsForRejectionList as $rfrData) {
            $rfr = $this->motTestReasonForRejectionService->createRfrFromData($rfrData, $motTest);
            $rfr->setGenerated(true);
            $motTest->addMotTestReasonForRejection($rfr);

            $tempComment = $rfr->popComment();
            $tempDescription = $rfr->popDescription();

            $this->entityManager->persist($rfr);
            $this->entityManager->flush();

            if ($tempComment instanceof MotTestReasonForRejectionComment) {
                $tempComment->setId($rfr->getId());
                $this->entityManager->persist($tempComment);
                $this->entityManager->flush();
            }

            if ($tempDescription instanceof MotTestReasonForRejectionDescription) {
                $tempDescription->setId($rfr->getId());
                $this->entityManager->persist($tempDescription);
                $this->entityManager->flush();
            }
        }

        $this->entityManager->persist($motTest);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param MotTest $motTest
     */
    public function deleteAllBrakeTestResults(MotTest $motTest)
    {
        $brakeTestsHelper = new BrakeTestResultsHelper($this->entityManager);
        $brakeTestsHelper->deleteAllBrakeTestResults($motTest);
    }

    public function validateBrakeTestConfiguration(MotTest $motTest, $brakeTestResultData)
    {
        $this->performMotTestAssertion->assertGranted($motTest);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        $vehicleClass = $motTest->getVehicle()->getVehicleClass()->getCode();

        switch ($vehicleClass) {
            case Vehicle::VEHICLE_CLASS_1:
            case Vehicle::VEHICLE_CLASS_2:
                /** @var BrakeTestResultClass12 $brakeTestResult */
                $brakeTestResult = $this->brakeTestResultClass12Mapper->mapToObject($brakeTestResultData);
                $this->brakeTestConfigurationValidator->validateBrakeTestConfigurationClass12($brakeTestResult);
                break;
            default:
                /** @var BrakeTestResultClass3AndAbove $brakeTestResult */
                $brakeTestResult = $this->brakeTestResultClass3AndAboveMapper->mapToObject($brakeTestResultData);
                $this->brakeTestConfigurationValidator->validateBrakeTestConfigurationClass3AndAbove(
                    $brakeTestResult,
                    $vehicleClass
                );
                break;
        }
    }

    /**
     * @param MotTest $motTest
     * @throws BadRequestException
     */
    private function assertMotActive(MotTest $motTest)
    {
        /* Only allow active MOT test brake test results to be validated */
        if ($motTest->getStatus() === MotTestStatusName::ABANDONED) {
            throw new BadRequestException(
                InvalidTestStatus::getMessage(MotTestStatusName::ABANDONED),
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        } elseif ($motTest->getStatus() !== MotTestStatusName::ACTIVE) {
            throw new BadRequestException(
                InvalidTestStatus::getMessage(MotTestStatusName::FAILED),
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }
    }

    private function validateAndCalculateBrakeTestResultClass3AndAbove($data, MotTest $motTest)
    {
        $this->assertMotActive($motTest);

        /** @var Vehicle $vehicle */
        $vehicle = $motTest->getVehicle();

        /** @var BrakeTestResultClass3AndAbove $brakeTestResult */
        $brakeTestResult = $this->brakeTestResultClass3AndAboveMapper->mapToObject($data);

        $this->brakeTestConfigurationValidator->validateBrakeTestConfigurationClass3AndAbove(
            $brakeTestResult,
            $vehicle->getVehicleClass()->getCode()
        );
        $this->brakeTestResultValidator->validateBrakeTestResultClass3AndAbove($brakeTestResult, $vehicle);

        /** @var BrakeTestClass3AndAboveCalculationResult $calculationResult  */
        $calculationResult = $this->brakeTestResultCalculator->calculateBrakeTestResult($brakeTestResult, $vehicle);
        $brakeTestResult = $calculationResult->getBrakeTestResultClass3AndAbove();

        $brakeTestResult = $this->mapVehicleWeightSource($brakeTestResult, $vehicle);

        $summary = new BrakeTestResultSubmissionSummary();
        $summary->brakeTestResultClass3AndAbove = $brakeTestResult;
        $summary->brakeTestResultClass1And2 = null;

        $serviceBrakeTestType = $brakeTestResult->getServiceBrake1TestType()->getCode();
        $parkingBrakeTestType = $brakeTestResult->getParkingBrakeTestType()->getCode();

        $this->generateServiceBrakeLowEfficiencyRfr($brakeTestResult, $summary, $serviceBrakeTestType, $calculationResult);

        $this->parkingBrakeMapper->generateParkingBrakeLowEfficiencyRfr($brakeTestResult, $summary, $parkingBrakeTestType, $calculationResult);

        $serviceBrake1Data = $brakeTestResult->getServiceBrake1Data();
        $serviceBrake2Data = $brakeTestResult->getServiceBrake2Data();

        for ($axleNumber = 1; $axleNumber <= self::MAX_NUMBER_AXLES; ++$axleNumber) {
            $isBrake1ImbalancePass = null;
            $isBrake2ImbalancePass = null;
            if ($serviceBrake1Data !== null) {
                $isBrake1ImbalancePass = $serviceBrake1Data->getImbalancePassForAxle($axleNumber);
            }
            if ($serviceBrake2Data !== null) {
                $isBrake2ImbalancePass = $serviceBrake2Data->getImbalancePassForAxle($axleNumber);
            }

            if ($isBrake1ImbalancePass === false || $isBrake2ImbalancePass === false) {
                $rfr = $this->serviceBrakeImbalanceMapper->generateServiceBrakeImbalanceRfr($serviceBrakeTestType,
                    $calculationResult->getBrakeImbalanceResult()->getAxleImbalanceSeverity(BrakeImbalanceResult::getAxleFromAxleNumber($axleNumber)));
                $type = ReasonForRejectionTypeName::FAIL;
                $location = $axleNumber === 1 ?
                    MotTestReasonForRejection::LOCATION_LONGITUDINAL_FRONT
                    : MotTestReasonForRejection::LOCATION_LONGITUDINAL_REAR;
                $comment = ($axleNumber >= 1) ? "Axle $axleNumber" : null;

                $summary->addReasonForRejection($rfr, $type, $location, $comment);
            }
        }

        if ($brakeTestResult->getParkingBrakeImbalancePass() === false) {
            $rfr = $this->parkingBrakeMapper->generateParkingBrakeImbalanceRfr($parkingBrakeTestType);
            $type = ReasonForRejectionTypeName::FAIL;

            $summary->addReasonForRejection($rfr, $type);
        }

        $brakeTestResult->setMotTest($motTest);

        return $summary;
    }

    private function validateAndCalculateBrakeTestResultClass1And2($data, MotTest $motTest)
    {
        $this->assertMotActive($motTest);

        /** @var BrakeTestResultClass12 $brakeTestResult */
        $brakeTestResult = $this->brakeTestResultClass12Mapper->mapToObject($data);

        $firstUsedDate = $motTest->getVehicle()->getFirstUsedDate();

        $this->brakeTestConfigurationValidator->validateBrakeTestConfigurationClass12($brakeTestResult);
        $this->brakeTestResultValidator->validateBrakeTestResultClass1And2($brakeTestResult, $firstUsedDate);

        $brakeTestResult = $this->brakeTestResultCalculatorClass1And2->calculateBrakeTestResult(
            $brakeTestResult,
            $firstUsedDate
        );
        $results = new BrakeTestResultSubmissionSummary();
        $results->brakeTestResultClass1And2 = $brakeTestResult;
        $results->brakeTestResultClass3AndAbove = null;
        $brakeTestType = $brakeTestResult->getBrakeTestType()->getCode();
        if ($this->brakeTestResultCalculatorClass1And2->areBothControlsUnderSecondaryMinimum($brakeTestResult)) {
            $results->addReasonForRejection($this->getRfrBothUnderSecondaryMin($brakeTestType));
        } else {
            if ($this->brakeTestResultCalculatorClass1And2->noControlReachesPrimaryMinimum($brakeTestResult)) {
                $results->addReasonForRejection($this->getRfrBothUnderPrimaryMin($brakeTestType));
            } else {
                if ($this->brakeTestResultCalculatorClass1And2
                    ->oneControlNotReachingSecondaryMinimum($brakeTestResult)
                ) {
                    $results->addReasonForRejection($this->getRfrOneUnderSecondaryMin($brakeTestType));
                }
            }
        }
        $brakeTestResult->setMotTest($motTest);

        return $results;
    }

    public function extract($brakeTestResult)
    {
        $brakeTestResultData = null;

        if ($brakeTestResult instanceof BrakeTestResultClass3AndAbove) {
            $brakeTestResultData = $this->objectHydrator->extract($brakeTestResult);
            if (in_array(
                $brakeTestResult->getParkingBrakeTestType()->getCode(),
                $this->brakesWithLockApplicable)
            ) {
                $brakeTestResultData['parkingBrakeLockPercent']
                    = $this->brakeTestResultCalculator->calculateParkingBrakePercentLocked($brakeTestResult);
            }
            $serviceBrake1Data = $brakeTestResult->getServiceBrake1Data();
            $serviceBrakeLocksApplicable = BrakeTestTypeConfiguration::areServiceBrakeLocksApplicable(
                $brakeTestResult->getMotTest()->getVehicleClass()->getCode(),
                $brakeTestResult->getServiceBrake1TestType()->getCode(),
                $brakeTestResult->getParkingBrakeTestType()->getCode()
            );
            if ($serviceBrake1Data) {
                $brakeTestResultData['serviceBrake1Data'] = $this->objectHydrator->extract($serviceBrake1Data);
                ExtractionHelper::unsetAuditColumns($brakeTestResultData['serviceBrake1Data']);
                if ($serviceBrakeLocksApplicable) {
                    $this->populateServiceBrakeLockPercent(
                        $brakeTestResultData['serviceBrake1Data'],
                        $serviceBrake1Data,
                        $brakeTestResult
                    );
                }
            }
            $brakeTestResultData['serviceBrake1TestType'] = $brakeTestResult->getServiceBrake1TestType()->getCode();
            $brakeTestResultData['serviceBrake2TestType'] = null;
            if ($brakeTestResult->getServiceBrake2TestType()) {
                $brakeTestResultData['serviceBrake2TestType'] = $brakeTestResult->getServiceBrake2TestType()->getCode();
            }
            $brakeTestResultData['weightType'] = null;
            if ($brakeTestResult->getWeightType()) {
                $brakeTestResultData['weightType'] = $brakeTestResult->getWeightType()->getCode();
            }
            $brakeTestResultData['parkingBrakeTestType'] = $brakeTestResult->getParkingBrakeTestType()->getCode();

            $serviceBrake2Data = $brakeTestResult->getServiceBrake2Data();
            if ($serviceBrake2Data) {
                $brakeTestResultData['serviceBrake2Data']
                    = $this->objectHydrator->extract($serviceBrake2Data);
                ExtractionHelper::unsetAuditColumns($brakeTestResultData['serviceBrake2Data']);
                if ($serviceBrakeLocksApplicable) {
                    $this->populateServiceBrakeLockPercent(
                        $brakeTestResultData['serviceBrake2Data'],
                        $serviceBrake2Data,
                        $brakeTestResult
                    );
                }
            }
        } else {
            if ($brakeTestResult instanceof BrakeTestResultClass12) {
                $brakeTestResultData = $this->objectHydrator->extract($brakeTestResult);
                ExtractionHelper::unsetAuditColumns($brakeTestResultData);

                if (BrakeTestConfigurationClass1And2::isLockApplicableToTestType(
                    $brakeTestResult->getBrakeTestType()->getCode())
                ) {
                    $brakeTestResultData['control1LockPercent']
                        = $this->brakeTestResultCalculatorClass1And2->calculateControl1PercentLocked($brakeTestResult);
                    $brakeTestResultData['control2LockPercent']
                        = $this->brakeTestResultCalculatorClass1And2->calculateControl2PercentLocked($brakeTestResult);
                }

                $brakeTestResultData['brakeTestType'] = $brakeTestResult->getBrakeTestType()->getCode();
            }
        }

        unset($brakeTestResultData['motTest']);

        return $brakeTestResultData;
    }

    private function populateServiceBrakeLockPercent(&$data, $serviceBrakeDataObject, $brakeTestResult)
    {
        $data['lockPercent'] = $this->brakeTestResultCalculator->calculateServiceBrakePercentLocked(
            $serviceBrakeDataObject,
            $brakeTestResult
        );
    }

    private function getRfrBothUnderSecondaryMin($testType)
    {
        switch ($testType) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_BRAKE_EFFICIENCY_ROLLER_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_BRAKE_EFFICIENCY_PLATE_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::FLOOR:
                return self::RFR_ID_BRAKE_EFFICIENCY_FLOOR_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_BRAKE_EFFICIENCY_DECELEROMETER_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::GRADIENT:
                return self::RFR_ID_BRAKE_EFFICIENCY_GRADIENT_BOTH_BELOW_SECONDARY_MIN_CLASS_1_2;
        }

        return null;
    }

    private function getRfrOneUnderSecondaryMin($brakeTestType)
    {
        switch ($brakeTestType) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_BRAKE_EFFICIENCY_ROLLER_ONE_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_BRAKE_EFFICIENCY_PLATE_ONE_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::FLOOR:
                return self::RFR_ID_BRAKE_EFFICIENCY_FLOOR_ONE_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_BRAKE_EFFICIENCY_DECELEROMETER_ONE_BELOW_SECONDARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::GRADIENT:
                return self::RFR_ID_BRAKE_EFFICIENCY_GRADIENT_ONE_BELOW_SECONDARY_MIN_CLASS_1_2;
        }

        return null;
    }

    private function getRfrBothUnderPrimaryMin($testType)
    {
        switch ($testType) {
            case BrakeTestTypeCode::ROLLER:
                return self::RFR_ID_BRAKE_EFFICIENCY_ROLLER_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::PLATE:
                return self::RFR_ID_BRAKE_EFFICIENCY_PLATE_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::FLOOR:
                return self::RFR_ID_BRAKE_EFFICIENCY_FLOOR_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::DECELEROMETER:
                return self::RFR_ID_BRAKE_EFFICIENCY_DECELEROMETER_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2;
            case BrakeTestTypeCode::GRADIENT:
                return self::RFR_ID_BRAKE_EFFICIENCY_GRADIENT_BOTH_BELOW_PRIMARY_MIN_CLASS_1_2;
        }

        return null;
    }

    /**
     * Map vehicle weight source if vehicle weight changed and new weight is from official source
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param Vehicle $vehicle
     * @return BrakeTestResultClass3AndAbove
     */
    private function mapVehicleWeightSource(BrakeTestResultClass3AndAbove $brakeTestResult, Vehicle $vehicle)
    {
        $currentVehicleWeight = $brakeTestResult->getVehicleWeight();
        $oldVehicleWeight = $vehicle->getWeight();

        if(is_null($currentVehicleWeight) || $currentVehicleWeight == $oldVehicleWeight) {
            return $brakeTestResult;
        }

        $weightSource = $brakeTestResult->getWeightType();

        $mapper = new BrakeTestWeightSourceMapper();

        if($mapper->isOfficialWeightSource($vehicle->getVehicleClass()->getCode(), $weightSource->getCode())) {
            $newWeightSourceCode = $mapper->mapOfficialWeightSourceToVehicleWeightSource(
                $vehicle->getVehicleClass()->getCode(),
                $brakeTestResult->getWeightType()->getCode()
            );
            $weightSource = $this->weightSourceRepository->getByCode($newWeightSourceCode);
        }

        $brakeTestResult->setWeightType($weightSource);

        return $brakeTestResult;
    }

    /**
     * @param BrakeTestResultClass3AndAbove $brakeTestResult
     * @param BrakeTestResultSubmissionSummary $summary
     * @param string $serviceBrakeTestTypeCode
     * @param BrakeTestClass3AndAboveCalculationResult $calculationResult
     */
    private function generateServiceBrakeLowEfficiencyRfr(
        BrakeTestResultClass3AndAbove $brakeTestResult,
        BrakeTestResultSubmissionSummary $summary,
        $serviceBrakeTestTypeCode,
        BrakeTestClass3AndAboveCalculationResult $calculationResult
    )
    {
        $isEnabledEuRoadworthiness = $this->featureToggles->isEnabled(FeatureToggle::EU_ROADWORTHINESS);
        $this->serviceBrakeRfrGenerator->generateRfr(
            $brakeTestResult,
            $summary,
            $serviceBrakeTestTypeCode,
            $calculationResult,
            $isEnabledEuRoadworthiness
        );
    }
}
