<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleUnderTestRequest;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\EmergencyLog;
use DvsaEntities\Entity\EmergencyReason;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\VehicleClass;
use DvsaEntities\Repository\ColourRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestStatusRepository;
use DvsaMotApi\Generator\MotTestNumberGenerator;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;

/**
 * Validates input data and creates MOT tests within the DB.
 */
class MotTestCreationHelper
{
    const COMMENT_EMERGENCY_LOG_TYPE = 'EMERGENCY_LOG';
    const ONE_TIME_PASSWORD_FIELD    = 'oneTimePassword';
    const ERROR_MSG_OVERDUE_SPECIAL_NOTICES = 'Your test status is inactive due to overdue acknowledgement of special notice(s). Your test status will become active when overdue special notices have been acknowledged.';

    /** @var EntityManager */
    private $entityManager;

    /** @var AuthorisationServiceInterface */
    private $authService;

    /** @var TesterService */
    private $testerService;

    /** @var MotTestRepository */
    private $motTestRepository;

    /** @var MotTestValidator */
    private $motTestValidator;

    /** @var RetestEligibilityValidator */
    private $retestEligibilityValidator;

    /** @var OtpService */
    private $otpService;
    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /** @var VehicleService */
    private $vehicleService;

    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        TesterService $testerService,
        $motTestRepository,
        MotTestValidator $motTestValidator,
        RetestEligibilityValidator $retestEligibilityValidator,
        OtpService $otpService,
        MotIdentityProviderInterface $identityProvider,
        VehicleService $vehicleService
    ) {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->testerService = $testerService;
        $this->motTestRepository = $motTestRepository;
        $this->motTestValidator = $motTestValidator;
        $this->retestEligibilityValidator = $retestEligibilityValidator;
        $this->otpService                 = $otpService;
        $this->identityProvider           = $identityProvider;
        $this->vehicleService = $vehicleService;
    }

    /**
     * @param \DvsaEntities\Entity\Person $tester
     * @param $vehicleId
     * @param $vtsId
     * @param $primaryColourCode
     * @param $secondaryColourCode
     * @param $fuelTypeCode
     * @param $vehicleClassCode
     * @param $hasRegistration
     * @param $motTestTypeCode
     * @param $motTestNumberOriginal
     * @param $complaintRef
     * @param $flagPrivate
     * @param $oneTimePassword
     * @param $contingencyId
     * @param $clientIp
     * @param \DvsaCommon\Dto\MotTesting\ContingencyTestDto $contingencyDto
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaAuthentication\Service\Exception\OtpException
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     * @throws \DvsaCommon\Date\Exception\NonexistentDateTimeException
     * @throws \Exception
     *
     * @return \DvsaEntities\Entity\MotTest
     */
    public function createMotTest(
        Person $tester,
        $vehicleId,
        $vtsId,
        $primaryColourCode,
        $secondaryColourCode,
        $fuelTypeCode,
        $vehicleClassCode,
        $hasRegistration,
        $motTestTypeCode,
        $motTestNumberOriginal,
        $complaintRef,
        $flagPrivate,
        $oneTimePassword,
        $contingencyId,
        $clientIp,
        ContingencyTestDto $contingencyDto = null
    ) {
        $isVehicleExaminer = $this->authService->personHasRole($tester, Role::VEHICLE_EXAMINER);

        // Assumption, that if we don't pass in an MOT Test Type, then it is a NORMAL (NT) Test?
        if (!$motTestTypeCode) {
            throw new \Exception('No MOT Test Type Code supplied');
        }

        /** @var \DvsaEntities\Repository\MotTestTypeRepository $motTestTypeRepository */
        $motTestTypeRepository = $this->entityManager->getRepository(MotTestType::class);

        /** @var \DvsaEntities\Entity\MotTestType $motTestType */
        $motTestType = $motTestTypeRepository->findOneByCode($motTestTypeCode);

        if (!$motTestType) {
            throw new \Exception('MOT Test Type not found by code: ' . $motTestTypeCode);
        }

        if (!$isVehicleExaminer && !$motTestType->getIsDemo()) {
            if ($tester->isTester()) {
                $this->checkTesterIsAllowedToTestClass($vehicleClassCode);
            } else {
                throw new NotFoundException('Tester with personId', $tester->getId());
            }
        }

        if ($motTestType->getIsDemo()) {
            $this->authService->assertGranted(PermissionInSystem::MOT_DEMO_TEST_PERFORM);
            $this->checkTesterHasNoInProgressDemoTest($tester->getId());
        } else {
            $this->checkForExistingInProgressTest($vehicleId);
            $this->checkTesterHasNoInProgressTest($tester->getId());
        }

        if ($motTestType->getCode() === MotTestTypeCode::RE_TEST) {
            $this->retestEligibilityValidator->checkEligibilityForRetest($vehicleId, $vtsId, $contingencyDto);

            // after validation there must be id in place
            $motTest = $this->motTestRepository->findLastNormalTest($vehicleId, $contingencyDto);
            $isDifferentVts = intval($motTest->getVehicleTestingStation()->getId()) !== intval($vtsId);
            if ($motTest->isCancelled() && $isDifferentVts) {
                $motTest = $this->motTestRepository->findLastNormalTest($vehicleId, $contingencyDto, $vtsId);
            }

            $motTestNumberOriginal = $motTest->getNumber();
        }

        if ($isVehicleExaminer
            && (
                $motTestType->getIsReinspection()
                || $motTestType->getCode() === MotTestTypeCode::NON_MOT_TEST
            )
        ) {
            $this->authService->assertGranted(PermissionInSystem::MOT_TEST_START);
        } elseif (!$motTestType->getIsDemo()) {
            $this->authService->assertGrantedAtSite(PermissionAtSite::MOT_TEST_START_AT_SITE, $vtsId);
        }

        $vts = $motTestType->getIsDemo() ? null : $this->entityManager->find(Site::class, $vtsId);

        /** @var Vehicle $vehicle */
        $vehicle = $this->entityManager->getRepository(Vehicle::class)->get($vehicleId);

        $primaryColour   = $primaryColourCode ? $this->getColourByCode($primaryColourCode) : null;
        $secondaryColour = $secondaryColourCode ? $this->getColourByCode($secondaryColourCode) : null;

        /** @var FuelType $fuelType */
        $fuelType        = $this->entityManager->getRepository(FuelType::class)->findOneByCode($fuelTypeCode);

        /** @var VehicleClass $vehicleClass */
        $vehicleClass    = $this->entityManager->getRepository(VehicleClass::class)->findOneByCode($vehicleClassCode);

        /** @var MotTestStatusRepository $motTestStatusRepository */
        $motTestStatusRepository = $this->entityManager->getRepository(MotTestStatus::class);
        $activeStatus            = $motTestStatusRepository->findActive();

        $motTest = new MotTest();
        $motTest
            ->setStatus($activeStatus)
            ->setTester($tester)
            ->setVehicle($vehicle)
            ->setVehicleTestingStation($vts)
            ->setPrimaryColour($primaryColour)
            ->setSecondaryColour($secondaryColour)
            ->setFuelType($fuelType)
            ->setVehicleClass($vehicleClass)
            ->setVin($vehicle->getVin())
            ->setRegistration($vehicle->getRegistration())
            ->setCountryOfRegistration($vehicle->getCountryOfRegistration())
            ->setHasRegistration($hasRegistration)
            ->setIsPrivate($flagPrivate)
            ->setMotTestType($motTestType)
            ->setEmptyVinReason($vehicle->getEmptyVinReason())
            ->setEmptyVrmReason($vehicle->getEmptyVrmReason())
            ->setClientIp($clientIp)
            ->setModelDetail($vehicle->getModelDetail());

        if ($vehicle->getModel()) {
            $motTest->setModel($vehicle->getModel());
        } else {
            $motTest->setFreeTextModelName($vehicle->getModelName());
        }

        if ($vehicle->getMake()) {
            $motTest->setMake($vehicle->getMake());
        } else {
            $motTest->setFreeTextMakeName($vehicle->getMakeName());
        }

        if ($contingencyDto instanceof ContingencyTestDto) {
            $motTest->setStartedDate($contingencyDto->getPerformedAt());

            /** @var \DvsaEntities\Entity\EmergencyLog $contingency */
            $contingency = $this->entityManager
                ->getRepository(EmergencyLog::class)->findOneBy(['id' => $contingencyId]);

            /** @var \DvsaEntities\Entity\EmergencyReason $contingencyReason */
            $contingencyReason = $this->entityManager
                ->getRepository(EmergencyReason::class)->findOneBy(['code' => $contingencyDto->getReasonCode()]);

            $motTest
                ->setEmergencyLog($contingency)
                ->setEmergencyReasonLookup($contingencyReason);

            if (!empty($contingencyDto->getOtherReasonText())) {
                $comment = new Comment();
                $comment
                    ->setComment($contingencyDto->getOtherReasonText())
                    ->setCommentAuthor($tester);

                $motTest->setEmergencyReasonComment($comment);
            }
        }

        $motTestOriginal = null;

        if (!empty($motTestNumberOriginal)) {
            /** @var \DvsaEntities\Entity\MotTest $motTestOriginal */
            $motTestOriginal = $this->entityManager
                ->getRepository(MotTest::class)->findOneBy(['number' => $motTestNumberOriginal]);
            $motTest->setMotTestIdOriginal($motTestOriginal);
        }

        if (!empty($complaintRef)) {
            $motTest->setComplaintRef($complaintRef);
        }

        $this->motTestValidator->validateNewMotTest($motTest);

        if ($this->isVehicleModified($vehicle, $vehicleClass, $fuelType, $primaryColour, $secondaryColour)
            && !$motTestType->getIsDemo()
        ) {
            if (!$this->identityProvider->getIdentity()->isSecondFactorRequired() &&
                !$this->authService->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP)
            ) {
                $this->otpService->authenticate($oneTimePassword);
            }

            // update vehicle with specified field values
            $updateDvsaVehicleUnderTestRequest = new UpdateDvsaVehicleUnderTestRequest();
            $updateDvsaVehicleUnderTestRequest->setColourId($primaryColour->getId())
                ->setSecondaryColourId($secondaryColour->getId())
                ->setFuelTypeId($fuelType->getId())
                ->setVehicleClassCode($vehicleClass->getCode())
//              ->setCylinderCapacity($data['CylinderCapacity'])   @todo: API and UI need to consider CC according to the certain fuel type
                ->setOneTimePassword($oneTimePassword);

            $this->vehicleService->updateDvsaVehicleUnderTest(
                $vehicle->getId(),
                $updateDvsaVehicleUnderTestRequest
            );
        }

        $this->entityManager->persist($motTest);
        if ($motTestType->getCode() === MotTestTypeCode::RE_TEST) {
            $this->saveRfrsForRetest($motTestOriginal, $motTest);
        }

        $this->entityManager->flush();

        // Regenerate the Test Number based on the DB row id.
        $motTest->setNumber(MotTestNumberGenerator::generateMotTestNumber($motTest->getId()));
        $this->entityManager->persist($motTest);
        $this->entityManager->flush();

        return $motTest;
    }

    /**
     * A vehicle can only have a single in progress test at a time.
     *
     * @param $vehicleId
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    private function checkForExistingInProgressTest($vehicleId)
    {
        $testExists = $this->motTestRepository->isTestInProgressForVehicle($vehicleId);
        if ($testExists) {
            $exception = BadRequestException::create();
            $errorMsg  = 'Vehicle already has an in progress test';
            $exception->addError($errorMsg, '', $errorMsg);
            throw $exception;
        }
    }

    private function checkTesterHasNoInProgressTest($personId)
    {
        if ($this->motTestRepository->findInProgressTestNumberForPerson($personId)) {
            throw new BadRequestException(
                'You have a test that is already in progress',
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }
    }

    private function checkTesterHasNoInProgressDemoTest($personId)
    {
        if ($this->motTestRepository->findInProgressDemoTestNumberForPerson($personId)) {
            throw new BadRequestException(
                'You have a demo test that is already in progress',
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }
    }

    private function checkTesterIsAllowedToTestClass($vehicleClassCode)
    {
        if ($this->testerService->verifyTesterAllowedToTestClass($vehicleClassCode) == false) {
            throw new ForbiddenException(self::ERROR_MSG_OVERDUE_SPECIAL_NOTICES);
        }
    }

    private function isVehicleModified(
        Vehicle $vehicle,
        VehicleClass $vehicleClass,
        FuelType $fuelType,
        $primaryColour,
        $secondaryColour
    ) {
        return !$this->isVehicleClassSame($vehicle->getVehicleClass(), $vehicleClass)
        || !$this->isFuelTypeSame($vehicle->getFuelType(), $fuelType)
        || !$this->isColourSame($vehicle->getColour(), $primaryColour)
        || !$this->isColourSame($vehicle->getSecondaryColour(), $secondaryColour);
    }

    /**
     * @param VehicleClass $vehicleClass1
     * @param VehicleClass $vehicleClass2
     *
     * @return bool
     */
    private static function isVehicleClassSame($vehicleClass1, $vehicleClass2)
    {
        if ($vehicleClass1 == null && $vehicleClass2 == null) {
            return true;
        }

        if ($vehicleClass1 != null && $vehicleClass2 != null) {
            return $vehicleClass1->getCode() === $vehicleClass2->getCode();
        }

        return false;
    }

    /**
     * @param FuelType $fuelType1
     * @param FuelType $fuelType2
     *
     * @return bool
     */
    private static function isFuelTypeSame($fuelType1, $fuelType2)
    {
        if ($fuelType1 == null && $fuelType2 == null) {
            return true;
        }

        if ($fuelType1 != null && $fuelType2 != null) {
            return $fuelType1->getId() == $fuelType2->getId();
        }

        return false;
    }

    /**
     * @param Colour $colour1
     * @param Colour $colour2
     *
     * @return bool
     */
    private static function isColourSame($colour1, $colour2)
    {
        if ($colour1 == null && $colour2 == null) {
            return true;
        }

        if ($colour1 != null && $colour2 != null) {
            return $colour1->getId() == $colour2->getId();
        }

        return false;
    }

    public function saveRfrsForRetest(MotTest $motTestOriginal, MotTest $motTest)
    {
        $rfrArrayOriginal = $motTestOriginal->getMotTestReasonForRejections();

        //Save RFRs to retest entity.
        /** @var MotTestReasonForRejection $rfrOriginal */
        foreach ($rfrArrayOriginal as $rfrOriginal) {
            if ($rfrOriginal->getType() != ReasonForRejectionTypeName::PRS) {
                $rfr = clone $rfrOriginal;
                $rfr->setMotTest($motTest);
                $rfr->setMotTestId($motTest->getId());
                $rfr->setOnOriginalTest(true);
                $motTest->addMotTestReasonForRejection($rfr);
                $this->entityManager->persist($rfr);
            }
        }
        $this->entityManager->persist($motTest);
    }

    private function getColourByCode($code)
    {
        /** @var ColourRepository $colourRepository */
        $colourRepository = $this->entityManager->getRepository(Colour::class);

        return $colourRepository->getByCode($code);
    }
}
