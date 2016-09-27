<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service\Mapper;

use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\FuelTypeDto;
use DvsaCommon\Formatting\DefectSentenceCaseConverter;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommon\Utility\AddressUtils;
use DvsaCommonApi\Service\Mapper\ColourMapper;
use DvsaCommonApi\Service\Mapper\FuelTypeMapper;
use DvsaCommonApi\Service\Mapper\MotTestTypeMapper;
use DvsaCommonApi\Service\Mapper\OdometerReadingMapper;
use DvsaCommonApi\Service\Mapper\ReasonForCancelMapper;
use DvsaCommonApi\Service\Mapper\VehicleClassMapper;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\CertificateExpiryService;
use DvsaMotApi\Service\Helper\ExtractionHelper;
use DvsaMotApi\Service\MotTestDateHelperService;
use DvsaMotApi\Service\MotTestStatusService;
use OrganisationApi\Service\Mapper\PersonMapper;
use VehicleApi\Service\Mapper\CountryOfRegistrationMapper;
use VehicleApi\Service\Mapper\VehicleMapper;
use VehicleApi\Service\VehicleSearchService;

/**
 * Class MotTestMapper.
 */
class MotTestMapper
{
    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;

    /**
     * @var CertificateExpiryService
     */
    private $certificateExpiryService;

    /**
     * @var MotTestStatusService
     */
    private $motTestStatusService;

    /**
     * @var MotTestDateHelperService
     */
    private $motTestDateService;

    /**
     * @var ParamObfuscator
     */
    private $paramObfuscator;

    /**
     * @var DefectSentenceCaseConverter
     */
    private $defectSentenceCaseConverter;

    /**
     * @var DoctrineObject
     */
    protected $objectHydrator;

    /**
     * @var BrakeTestResultService
     */
    protected $brakeTestResultService;

    /**
     * @var VehicleSearchService
     */
    protected $vehicleSearchService;

    /**
     * MotTestMapper constructor.
     *
     * @param DoctrineObject              $objectHydrator
     * @param BrakeTestResultService      $brakeTestResultService
     * @param VehicleSearchService        $vehicleService
     * @param CertificateExpiryService    $certificateExpiryService
     * @param MotTestStatusService        $motTestStatusService
     * @param MotTestDateHelperService    $motTestDateService
     * @param ParamObfuscator             $paramObfuscator
     * @param DefectSentenceCaseConverter $defectSentenceCaseConverter
     */
    public function __construct(DoctrineObject $objectHydrator, BrakeTestResultService $brakeTestResultService,
                                VehicleSearchService $vehicleService, CertificateExpiryService $certificateExpiryService,
                                MotTestStatusService $motTestStatusService, MotTestDateHelperService $motTestDateService,
                                ParamObfuscator $paramObfuscator, DefectSentenceCaseConverter $defectSentenceCaseConverter)
    {
        $this->objectHydrator = $objectHydrator;
        $this->brakeTestResultService = $brakeTestResultService;
        $this->vehicleSearchService = $vehicleService;
        $this->dateTimeHolder = new DateTimeHolder();
        $this->certificateExpiryService = $certificateExpiryService;
        $this->motTestStatusService = $motTestStatusService;
        $this->motTestDateService = $motTestDateService;
        $this->paramObfuscator = $paramObfuscator;
        $this->defectSentenceCaseConverter = $defectSentenceCaseConverter;
    }

    /**
     * @param MotTest $motTest
     * @param bool    $extractOriginalMotTest
     *
     * @throws \Exception
     *
     * @return MotTestDto
     */
    public function mapMotTest(MotTest $motTest, $extractOriginalMotTest = true)
    {
        $result = $this->mapMotTestMinimal($motTest, $extractOriginalMotTest);

        $result->setEmergencyReasonComment($motTest->getEmergencyReasonComment());

        if (!is_null($motTest->getEmptyVrmReason())) {
            $result->setEmptyVrmReason($motTest->getEmptyVrmReason()->getCode());
        }
        if (!is_null($motTest->getEmptyVinReason())) {
            $result->setEmptyVinReason($motTest->getEmptyVinReason()->getCode());
        }

        $result->setClientIp($motTest->getClientIp());

        if ($motTest->getVehicleTestingStation()) {
            $site = $motTest->getVehicleTestingStation();

            $siteJson = $result->getVehicleTestingStation();

            $comments = [];
            foreach ($site->getSiteComments() as $siteComment) {
                $comments[] = $this->objectHydrator->extract($siteComment->getComment());
            }
            $siteJson['comments'] = $comments;

            $result->setVehicleTestingStation($siteJson);
        }

        $rfrs = $motTest->getMotTestReasonForRejections();
        $result->setReasonsForRejection($this->getMotReasonsForRejectionStringsGroupedByType($rfrs));
        $result->setTesterBrakePerformanceNotTested(
            $this->motTestStatusService->hasBrakePerformanceNotTestedRfr($motTest)
        );

        if ($motTest->getMotTestReasonForCancel()) {
            $reasonForCancel = $motTest->getMotTestReasonForCancel();
            $result->setReasonForCancel((new ReasonForCancelMapper())->toDto($reasonForCancel));
        }

        $result->setReasonForTerminationComment($motTest->getReasonForTerminationComment());

        if ($motTest->getFullPartialRetest()) {
            $result->setFullPartialRetest($this->objectHydrator->extract($motTest->getFullPartialRetest()));
        }

        if ($motTest->getPartialReinspectionComment()) {
            $result->setPartialReinspectionComment(
                $this->objectHydrator->extract(
                    $motTest->getPartialReinspectionComment()
                )
            );
        }

        if ($motTest->getItemsNotTestedComment()) {
            $result->setItemsNotTestedComment(
                $this->objectHydrator->extract(
                    $motTest->getItemsNotTestedComment()
                )
            );
        }

        if (is_null($motTest->getEmergencyLog()) === false) {
            $result->setEmergencyLog(
                [
                    'id'          => $motTest->getEmergencyLog()->getId(),
                    'number'      => $motTest->getEmergencyLog()->getNumber(),
                    'description' => $motTest->getEmergencyLog()->getDescription(),
                    'startedDate' => $motTest->getEmergencyLog()->getStartDate(),
                    'endedDate'   => $motTest->getEmergencyLog()->getEndDate(),
                ]
            );

            $result->setEmergencyReasonLookup(
                [
                    'id'          => $motTest->getEmergencyReasonLookup()->getId(),
                    'code'        => $motTest->getEmergencyReasonLookup()->getCode(),
                    'name'        => $motTest->getEmergencyReasonLookup()->getName(),
                    'description' => $motTest->getEmergencyReasonLookup()->getDescription(),
                ]
            );

            if (is_null($motTest->getEmergencyReasonComment()) === false) {
                $result->setEmergencyReasonComment(
                    [
                        'id'      => $motTest->getEmergencyReasonComment()->getId(),
                        'comment' => $motTest->getEmergencyReasonComment()->getComment(),
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * @param MotTest $motTest
     * @param bool    $extractOriginalMotTest
     *
     * @throws \Exception
     *
     * @return MotTestDto
     */
    public function mapMotTestMinimal(MotTest $motTest, $extractOriginalMotTest = true)
    {
        /** @var MotTestDto $result */
        $result = (new MotTestDto())
            ->setComplaintRef($motTest->getComplaintRef())
            ->setCountryOfRegistration(
                $motTest->getCountryOfRegistration()
                    ? (new CountryOfRegistrationMapper())->toDto($motTest->getCountryOfRegistration())
                    : null
            )
            ->setDocument($motTest->getDocument())
            ->setFuelType(
                $motTest->getFuelType()
                    ? (new FuelTypeMapper())->toDto($motTest->getFuelType(), FuelTypeDto::class)
                    : null
            )
            ->setHasRegistration($motTest->getHasRegistration())
            ->setId($motTest->getId())
            ->setIsPrivate($motTest->getIsPrivate())
            ->setMake($motTest->getMakeName())
            ->setModel($motTest->getModelName())
            ->setOdometerReading(
                $motTest->getOdometerReading()
                    ? (new OdometerReadingMapper())->toDto($motTest->getOdometerReading())
                    : null
            )
            ->setOnePersonReInspection($motTest->getOnePersonReInspection())
            ->setOnePersonTest($motTest->getOnePersonTest())
            ->setPrimaryColour(
                $motTest->getPrimaryColour()
                    ? (new ColourMapper())->toDto($motTest->getPrimaryColour())
                    : null
            )
            ->setRegistration($motTest->getRegistration())
            ->setSecondaryColour(
                $motTest->getSecondaryColour()
                    ? (new ColourMapper())->toDto($motTest->getSecondaryColour())
                    : null
            )
            ->setStatus($motTest->getStatus())
            ->setTestType(
                $motTest->getMotTestType()
                    ? (new MotTestTypeMapper())->toDto($motTest->getMotTestType())
                    : null
            )
            ->setTester($motTest->getTester() ? (new PersonMapper())->toDto($motTest->getTester()) : null)
            ->setVehicle($motTest->getVehicle() ? (new VehicleMapper())->toDto($motTest->getVehicle()) : null)
            ->setVehicleClass(
                $motTest->getVehicleClass() !== null
                    ? (new VehicleClassMapper())->toDto($motTest->getVehicleClass())
                    : null
            )
            ->setVin($motTest->getVin());

        if ($motTest->getVehicleTestingStation()) {
            $site = $motTest->getVehicleTestingStation();

            $siteJson = $this->objectHydrator->extract($site);
            ExtractionHelper::unsetAuditColumns($siteJson);

            $siteJson['address'] = ($site->getAddress()
                ? AddressUtils::stringify($site->getAddress())
                : null
            );

            if ($contact = $site->getBusinessContact()) {
                $phone = $contact->getDetails()->getPrimaryPhone();
                $siteJson['primaryTelephone'] = (is_object($phone) ? $phone->getNumber() : null);
            } else {
                $siteJson['primaryTelephone'] = null;
            }

            $ae = $motTest->getVehicleTestingStation()->getAuthorisedExaminer();
            $siteJson['authorisedExaminer'] = ($ae ? $ae->getId() : null);

            unset($siteJson['roles']);

            $defaultServiceBrakeTestClass3AndAbove =  $site->getDefaultServiceBrakeTestClass3AndAbove();
            if ($defaultServiceBrakeTestClass3AndAbove) {
                $siteJson['defaultServiceBrakeTestClass3AndAbove'] = $defaultServiceBrakeTestClass3AndAbove->getCode();
            }

            $defaultParkingBrakeTestClass3AndAbove =  $site->getDefaultParkingBrakeTestClass3AndAbove();
            if ($defaultParkingBrakeTestClass3AndAbove) {
                $siteJson['defaultParkingBrakeTestClass3AndAbove'] = $defaultParkingBrakeTestClass3AndAbove->getCode();
            }

            $result->setVehicleTestingStation($siteJson);
        }

        $brakeTestResult = null;

        switch ($motTest->getVehicle()->getModelDetail()->getVehicleClass()->getCode()) {
            case Vehicle::VEHICLE_CLASS_1:
            case Vehicle::VEHICLE_CLASS_2:
                $brakeTestResult = $motTest->getBrakeTestResultClass12();
                break;
            default:
                $brakeTestResult = $motTest->getBrakeTestResultClass3AndAbove();
                break;
        }
        if ($brakeTestResult) {
            $result->setBrakeTestResult($this->brakeTestResultService->extract($brakeTestResult));
        }

        $result->setBrakeTestCount($motTest->getBrakeTestCount());

        if ($motTest->isActive()) {
            $result->setPendingDetails([
                'currentSubmissionStatus' => $this->motTestStatusService->getMotTestPendingStatus($motTest),
                'issuedDate' => DateTimeApiFormat::date($this->motTestDateService->getIssuedDate($motTest)),
                'expiryDate' => DateTimeApiFormat::date($this->motTestDateService->getExpiryDate($motTest, null,
                    $this->motTestStatusService->getMotTestPendingStatus($motTest))),
            ]);
        }

        $result->setStartedDate(DateTimeApiFormat::dateTime($motTest->getStartedDate()));
        $result->setCompletedDate(DateTimeApiFormat::dateTime($motTest->getCompletedDate()));
        $result->setIssuedDate(DateTimeApiFormat::dateTime($motTest->getIssuedDate()));
        $result->setExpiryDate(DateTimeApiFormat::date($motTest->getExpiryDate()));

        if ($extractOriginalMotTest) {
            $originalMotTest = $motTest->getMotTestIdOriginal();
            if ($originalMotTest) {
                $result->setMotTestOriginal($this->mapMotTest($originalMotTest, false)); //false prevents recursion
            }
        }

        $prsMotTest = $motTest->getPrsMotTest();
        if ($prsMotTest) {
            $result->setPrsMotTestNumber($prsMotTest ? $prsMotTest->getNumber() : null);
        }

        $result->setMotTestNumber($motTest->getNumber());

        return $result;
    }

    /**
     * @param MotTestReasonForRejection[] $motRfrs
     * @param bool                        $short
     *
     * @return array
     */
    private function getMotReasonsForRejectionStringsGroupedByType($motRfrs, $short = false)
    {
        $motRfrsGroupedByTypes = [];

        foreach ($motRfrs as $motRfr) {
            if (!array_key_exists($motRfr->getType(), $motRfrsGroupedByTypes)) {
                $motRfrsGroupedByTypes[$motRfr->getType()] = [];
            }
            if ($short) {
                $motRfrsGroupedByTypes[$motRfr->getType()][] = $motRfr->getEnglishName();
            } else {
                $currentRfr = $this->hydrateTestRfr($motRfr);
                $motRfrsGroupedByTypes[$motRfr->getType()][] = $currentRfr;
            }
        }

        return $motRfrsGroupedByTypes;
    }

    /**
     * @param MotTestReasonForRejection $motTestRfr
     *
     * @return array
     */
    private function hydrateTestRfr(MotTestReasonForRejection $motTestRfr)
    {
        $hydratedTestRfr = $this->objectHydrator->extract($motTestRfr);
        $hydratedTestRfr['markedAsRepaired'] = $motTestRfr->isMarkedAsRepaired();
        unset($hydratedTestRfr['motTest']);
        unset($hydratedTestRfr['motTestId']);
        ExtractionHelper::unsetAuditColumns($hydratedTestRfr);
        unset($hydratedTestRfr['reasonForRejection']);

        $rfrEntity = $motTestRfr->getReasonForRejection();
        if (is_null($rfrEntity)) {
            // TODO VM-3386 - resolve manual advisories problem
            $hydratedTestRfr['name'] =  'Manual Advisory';
            $hydratedTestRfr['nameCy'] = 'Cynghori Llawlyfr';
            $hydratedTestRfr['failureText'] = '';
            $hydratedTestRfr['failureTextCy'] = '';
        } else {
            $hydratedTestRfr['rfrId'] = $rfrEntity->getRfrId();
            $hydratedTestRfr['testItemSelectorId'] = $rfrEntity->getTestItemSelector()->getId();
            $hydratedTestRfr['inspectionManualReference'] = $rfrEntity->getInspectionManualReference();
            /** @var array $formattedDescriptions */
            $formattedDescriptions = $this->defectSentenceCaseConverter->formatRfrDescriptionsForTestResultsAndBasket($rfrEntity, $motTestRfr->getType());
            if (!empty($formattedDescriptions['testItemSelectorDescription'])) {
                $hydratedTestRfr['testItemSelectorDescription'] = $formattedDescriptions['testItemSelectorDescription'];
            }
            if (!empty($formattedDescriptions['failureText'])) {
                $hydratedTestRfr['failureText'] = $formattedDescriptions['failureText'];
            }
        }

        return $hydratedTestRfr;
    }
}
