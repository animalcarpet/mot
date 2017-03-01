<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Request\UpdateDvsaVehicleUnderTestRequest;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Role;
use DvsaCommon\Dto\MotTesting\ContingencyTestDto;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Model\FuelTypeAndCylinderCapacity;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Colour;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\EmergencyLog;
use DvsaEntities\Entity\EmergencyReason;
use DvsaEntities\Entity\FuelType;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestComplaintRef;
use DvsaEntities\Entity\MotTestEmergencyReason;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionComment;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
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
use Exception;

/**
 * Validates input data and creates MOT tests within the DB.
 */
class MotTestCreationHelper
{
    const COMMENT_EMERGENCY_LOG_TYPE = 'EMERGENCY_LOG';
    const ONE_TIME_PASSWORD_FIELD = 'oneTimePassword';
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

    /** @var MotIdentityProviderInterface */
    private $identityProvider;

    /** @var VehicleService */
    private $vehicleService;

    /**
     * MotTestCreationHelper constructor.
     *
     * @param EntityManager                 $entityManager
     * @param AuthorisationServiceInterface $authService
     * @param TesterService                 $testerService
     * @param                               $motTestRepository
     * @param MotTestValidator              $motTestValidator
     * @param RetestEligibilityValidator    $retestEligibilityValidator
     * @param MotIdentityProviderInterface  $identityProvider
     * @param VehicleService                $vehicleService
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        TesterService $testerService,
        $motTestRepository,
        MotTestValidator $motTestValidator,
        RetestEligibilityValidator $retestEligibilityValidator,
        MotIdentityProviderInterface $identityProvider,
        VehicleService $vehicleService
    )
    {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->testerService = $testerService;
        $this->motTestRepository = $motTestRepository;
        $this->motTestValidator = $motTestValidator;
        $this->retestEligibilityValidator = $retestEligibilityValidator;
        $this->identityProvider = $identityProvider;
        $this->vehicleService = $vehicleService;
    }

    /**
     * @param Person $tester
     * @param int $vehicleId
     * @param int $vtsId
     * @param string $vehicleClassCode
     * @param boolean $hasRegistration
     * @param string $motTestTypeCode
     * @param string $motTestNumberOriginal
     * @param string $clientIp
     * @param int $contingencyId
     * @param ContingencyTestDto|null $contingencyDto
     * @param MotTestComplaintRef|null $complaintRef
     *
     * @return MotTest
     * @throws NotFoundException
     * @throws \Exception
     */
    public function createMotTest(
        Person $tester,
        $vehicleId,
        $vtsId,
        $vehicleClassCode,
        $hasRegistration,
        $motTestTypeCode,
        $motTestNumberOriginal,
        $clientIp,
        $contingencyId,
        ContingencyTestDto $contingencyDto = null,
        MotTestComplaintRef $complaintRef = null
    )
    {
        $isVehicleExaminer = $this->authService->personHasRole($tester, Role::VEHICLE_EXAMINER);
        $motTestType = $this->getMotTestType($motTestTypeCode);

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

        if ($motTestType->getIsDemo() || $motTestType->isNonMotTest()) {
            $vts = $organisation = null;
        } else {
            /** @var Site $vts */
            $vts = $this->entityManager->find(Site::class, $vtsId);
            $organisation = $vts->getOrganisation();
        }

        /** @var Vehicle $vehicle */
        $vehicle = $this->entityManager->getRepository(Vehicle::class)->get($vehicleId);

        /** @var MotTestStatusRepository $motTestStatusRepository */
        $motTestStatusRepository = $this->entityManager->getRepository(MotTestStatus::class);
        $activeStatus = $motTestStatusRepository->findActive();

        $newMotTest = new MotTest();
        $newMotTest
            ->setStatus($activeStatus)
            ->setTester($tester)
            ->setVehicle($vehicle)
            ->setVehicleVersion($vehicle->getVersion())
            ->setVehicleTestingStation($vts)
            ->setHasRegistration($hasRegistration)
            ->setMotTestType($motTestType)
            ->setClientIp($clientIp)
            ->setSubmittedDate(new \DateTime('now'))
            ->setOrganisation($organisation);

        $motTestOriginal = null;

        if (!empty($motTestNumberOriginal)) {
            /** @var \DvsaEntities\Entity\MotTest $motTestOriginal */
            $motTestOriginal = $this->entityManager
                ->getRepository(MotTest::class)->findOneBy(['number' => $motTestNumberOriginal]);
            $newMotTest->setMotTestIdOriginal($motTestOriginal);
        }

        $this->motTestValidator->validateNewMotTest($newMotTest);

        $this->entityManager->persist($newMotTest);
        $this->entityManager->flush();

        if (!empty($complaintRef)) {
            $complaintRef->setId($newMotTest->getId());
            $newMotTest->setComplaintRef($complaintRef);
        }

        if ($motTestType->getCode() === MotTestTypeCode::RE_TEST) {
            $this->saveRfrsForRetest($motTestOriginal, $newMotTest);
        }

        $this->entityManager->flush();

        // Regenerate the Test Number based on the DB row id.
        $newMotTest->setNumber(MotTestNumberGenerator::generateMotTestNumber($newMotTest->getId()));

        if ($contingencyDto instanceof ContingencyTestDto) {
            $newMotTest->setStartedDate($contingencyDto->getPerformedAt());

            /** @var \DvsaEntities\Entity\EmergencyLog $contingency */
            $contingency = $this->entityManager
                ->getRepository(EmergencyLog::class)->findOneBy(['id' => $contingencyId]);

            /** @var \DvsaEntities\Entity\EmergencyReason $contingencyReason */
            $contingencyReason = $this->entityManager
                ->getRepository(EmergencyReason::class)->findOneBy(['code' => $contingencyDto->getReasonCode()]);

            $motTestEmergencyReason = new MotTestEmergencyReason();

            $motTestEmergencyReason
                ->setId($newMotTest->getId())
                ->setLastUpdatedBy($newMotTest->getLastAmendedBy())
                ->setLastUpdatedOn($newMotTest->getLastAmendedOn())
                ->setEmergencyLog($contingency)
                ->setEmergencyReason($contingencyReason);

            if (!empty($contingencyDto->getOtherReasonText())) {
                $comment = new Comment();
                $comment
                    ->setComment($contingencyDto->getOtherReasonText())
                    ->setCommentAuthor($tester);

                $motTestEmergencyReason->setComment($comment);
            }

            $this->entityManager->persist($motTestEmergencyReason);
            $this->entityManager->flush();
            $newMotTest->setMotTestEmergencyReason($motTestEmergencyReason);
        }

        $this->entityManager->persist($newMotTest);
        $this->entityManager->flush();

        return $newMotTest;
    }

    /**
     * @param string $fuelTypeCode
     * @param string $cylinderCapacity
     * @param string $vehicleMake
     * @param string $vehicleModel
     * @param int    $vehicleId
     * @param string $vehicleClassCode
     * @param string $fuelTypeCode
     * @param string $primaryColourCode
     * @param string $secondaryColourCode
     * @param $updatedCountryOfRegistrationId
     * @param string $motTestTypeCode
     *
     * @return DvsaVehicle
     *
     * @throws Exception
     */
    public function updateVehicleIfChanged($fuelTypeCode, $cylinderCapacity, $vehicleMake, $vehicleModel, $vehicleId, $vehicleClassCode, $fuelTypeCode, $primaryColourCode, $secondaryColourCode, $updatedCountryOfRegistrationId, $motTestTypeCode)
    {
        $vehicle = $this->entityManager->getRepository(Vehicle::class)->get($vehicleId);
        $vehicleClass = $this->entityManager->getRepository(VehicleClass::class)->findOneByCode($vehicleClassCode);
        $fuelType = $this->entityManager->getRepository(FuelType::class)->findOneByCode($fuelTypeCode);
        $primaryColour = $primaryColourCode ? $this->getColourByCode($primaryColourCode) : null;
        $secondaryColour = $secondaryColourCode ? $this->getColourByCode($secondaryColourCode) :
            $this->getColourByCode(ColourCode::NOT_STATED);
        $motTestType = $this->getMotTestType($motTestTypeCode);
        $countryOfRegistrationId = $updatedCountryOfRegistrationId ? $updatedCountryOfRegistrationId : $vehicle->getCountryOfRegistration()->getId();

        if ($this->isVehicleModified($vehicle, $vehicleClass, $fuelType, $primaryColour, $secondaryColour, $cylinderCapacity, $vehicleMake, $vehicleModel, $updatedCountryOfRegistrationId) && !$motTestType->getIsDemo()) {
            // update vehicle with specified field values
            $updateDvsaVehicleUnderTestRequest = new UpdateDvsaVehicleUnderTestRequest();
            $updateDvsaVehicleUnderTestRequest->setColourCode($primaryColour->getCode())
                ->setSecondaryColourCode($secondaryColour->getCode())
                ->setVehicleClassCode($vehicleClass->getCode())
                ->setFuelTypeCode($fuelTypeCode)
                ->setCountryOfRegistrationId($countryOfRegistrationId);

            if (FuelTypeAndCylinderCapacity::isCylinderCapacityCompulsoryForFuelTypeCode($fuelTypeCode)) {
                $updateDvsaVehicleUnderTestRequest->setCylinderCapacity($cylinderCapacity);
            }

            if (!is_null($vehicleMake)) {
                if ($vehicleMake['makeId'] == 'other') {
                    $updateDvsaVehicleUnderTestRequest->setMakeOther($vehicleMake['makeName']);
                } else {
                    $updateDvsaVehicleUnderTestRequest->setMakeId($vehicleMake['makeId']);
                }
            }

            if (!is_null($vehicleModel)) {
                if ($this->shouldUpdateModelOther($vehicleModel)) {
                    $updateDvsaVehicleUnderTestRequest->setModelOther($vehicleModel['modelName']);
                } else {
                    $updateDvsaVehicleUnderTestRequest->setModelId($vehicleModel['modelId']);
                }
            }

            $updatedVehicle = $this->vehicleService->updateDvsaVehicleUnderTest(
                $vehicle->getId(),
                $updateDvsaVehicleUnderTestRequest
            );

            // The vehicle was changed in an external call and we need to refresh it locally.
            $this->entityManager->refresh($vehicle);

            return $updatedVehicle;
        }
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
            $errorMsg = 'Vehicle already has an in progress test';
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
        $secondaryColour,
        $cylinderCapacity,
        $vehicleMake,
        $vehicleModel,
        $countryOfRegistration
    ) {
        return !$this->isVehicleClassSame($vehicle->getVehicleClass(), $vehicleClass)
        || !$this->isFuelTypeSame($vehicle->getFuelType(), $fuelType)
        || !$this->isColourSame($vehicle->getColour(), $primaryColour)
        || !$this->isColourSame($vehicle->getSecondaryColour(), $secondaryColour)
        || !$this->isCylinderCapacitySame($vehicle->getCylinderCapacity(), $cylinderCapacity)
        || !$this->isMakeSame($vehicle->getMakeName(), $vehicleMake['makeName'])
        || !$this->isModelSame($vehicle->getModelName(), $vehicleModel['modelName'])
        || !$this->isCountryOfRegistrationSame($vehicle->getCountryOfRegistration()->getId(), $countryOfRegistration);
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

    /**
     * @param $original
     * @param $changedValue
     *
     * @return bool
     */
    private function isCylinderCapacitySame($original, $changedValue)
    {
        return $this->isSame($original, $changedValue);
    }

    /**
     * @param $original
     * @param $changedValue
     *
     * @return bool
     */
    private function isMakeSame($original, $changedValue)
    {
        return $this->isSame($original, $changedValue);
    }

    /**
     * @param $original
     * @param $changedValue
     *
     * @return bool
     */
    private function isModelSame($original, $changedValue)
    {
        return $this->isSame($original, $changedValue);
    }

    /**
     * @param $original
     * @param $changedValue
     *
     * @return bool
     */
    private function isSame($original, $changedValue)
    {
        if ($original == null || $changedValue == null) {
            return true;
        }

        if ($original != null && $changedValue != null) {
            return $original == $changedValue;
        }

        return false;
    }

    /**
     * @param $original
     * @param $changedValue
     *
     * @return bool
     */
    private function isCountryOfRegistrationSame($original, $changedValue)
    {
        $this->isSame($original, $changedValue);
    }


    public function saveRfrsForRetest(MotTest $motTestOriginal, MotTest $newMotTest)
    {
        $rfrArrayOriginal = $motTestOriginal->getMotTestReasonForRejections();

        //Save RFRs to retest entity.
        /** @var MotTestReasonForRejection $rfrOriginal */
        foreach ($rfrArrayOriginal as $rfrOriginal) {
            if ($rfrOriginal->getType()->getReasonForRejectionType() != ReasonForRejectionTypeName::PRS) {
                $rfr = clone $rfrOriginal;

                $originalComment = $originalDescription = null;

                if ($rfr->getMotTestReasonForRejectionComment()) {
                    $originalComment = clone $rfr->popComment();
                }

                if ($rfr->getCustomDescription()) {
                    $originalDescription = clone $rfr->popDescription();
                }

                $rfr->setMotTest($newMotTest);
                $rfr->setMotTestId($newMotTest->getId());
                $rfr->setOnOriginalTest(true);
                $newMotTest->addMotTestReasonForRejection($rfr);

                $this->entityManager->persist($rfr);
                $this->entityManager->flush();

                if ($originalComment instanceof MotTestReasonForRejectionComment) {

                    $newComment = new MotTestReasonForRejectionComment();
                    $newComment->setComment($originalComment->getComment())
                        ->setId($rfr->getId());

                    $this->entityManager->persist($newComment);
                    $this->entityManager->flush();
                }

                if ($originalDescription instanceof MotTestReasonForRejectionDescription) {

                    $newDescription = new MotTestReasonForRejectionDescription();

                    $newDescription->setCustomDescription($originalDescription->getCustomDescription())
                        ->setId($rfr->getId());

                    $this->entityManager->persist($newDescription);
                    $this->entityManager->flush();
                }

                $this->entityManager->flush();
            }
        }
        $this->entityManager->persist($newMotTest);
    }

    private function getColourByCode($code)
    {
        /** @var ColourRepository $colourRepository */
        $colourRepository = $this->entityManager->getRepository(Colour::class);

        return $colourRepository->getByCode($code);
    }

    /**
     * @param array $vehicleModel
     *
     * @return bool
     */
    private function shouldUpdateModelOther($vehicleModel)
    {
        return ($vehicleModel['modelId'] == 'other'
            || $vehicleModel['modelId'] == 'otherModel'
        );
    }

    /**
     * @param string $motTestTypeCode
     *
     * @return MotTestType
     *
     * @throws Exception
     */
    private function getMotTestType($motTestTypeCode)
    {
        if (!$motTestTypeCode) {
            throw new Exception('No MOT Test Type Code supplied');
        }

        $motTestType = $this->entityManager->getRepository(MotTestType::class)->findOneByCode($motTestTypeCode);

        if (!$motTestType) {
            throw new Exception('MOT Test Type not found by code: ' . $motTestTypeCode);
        }

        return $motTestType;
    }
}
