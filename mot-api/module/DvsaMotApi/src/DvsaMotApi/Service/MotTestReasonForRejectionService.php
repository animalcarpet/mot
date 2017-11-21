<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\DataValidationException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionComment;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
use DvsaEntities\Entity\MotTestReasonForRejectionLocation;
use DvsaEntities\Entity\MotTestReasonForRejectionMarkedAsRepaired;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Repository\MotTestReasonForRejectionLocationRepository;
use DvsaEntities\Repository\MotTestReasonForRejectionRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Repository\ReasonForRejectionTypeRepository;
use DvsaEntities\Repository\ReasonForRejectionTypeRepositoryInterface;
use DvsaEntities\Repository\RfrRepository;
use DvsaMotApi\Service\Helper\BrakeTestResultsHelper;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaCommon\Constants\ReasonForRejection as ReasonForRejectionConstants;

class MotTestReasonForRejectionService
{
    const LONGITUDINAL_LOCATION_FIELD = 'locationLongitudinal';
    const VERRTICAL_LOCATION_FIELD = 'locationVertical';
    const LATERAL_LOCATION_FIELD = 'locationLateral';
    const FAILURE_DANGEROUS_FIELD = 'failureDangerous';
    const GENERATED_FIELD = 'generated';
    const COMMENT_FIELD = 'comment';
    const RFR_ID_FIELD = 'rfrId';
    const TYPE_FIELD = 'type';

    /** @var MotTestValidator */
    protected $motTestValidator;

    /** @var AuthorisationServiceInterface */
    protected $authService;

    /** @var ApiPerformMotTestAssertion */
    private $performMotTestAssertion;

    /** @var MotTestRepository */
    private $motTestRepository;

    /** @var EntityManager */
    private $entityManager;

    /** @var RfrRepository */
    private $rfrRepository;

    /** @var MotTestReasonForRejectionRepository */
    private $motTestReasonForRejectionRepository;

    /** @var MotTestReasonForRejectionLocationRepository */
    private $motTestReasonForRejectionLocationRepository;

    /** @var ReasonForRejectionTypeRepository $reasonForRejectionTypeRepository */
    private $reasonForRejectionTypeRepository;

    /** @var BrakeTestResultsHelper $brakeTestResultsHelper */
    private $brakeTestResultsHelper;

    /**
     * MotTestReasonForRejectionService constructor.
     *
     * @param EntityManager $entityManager
     * @param AuthorisationServiceInterface $authService
     * @param MotTestValidator $motTestValidator
     * @param ApiPerformMotTestAssertion $performMotTestAssertion
     * @param MotTestRepository $motTestRepository
     * @param RfrRepository $rfrRepository
     * @param MotTestReasonForRejectionRepository $motTestReasonForRejectionRepository
     * @param MotTestReasonForRejectionLocationRepository $motTestReasonForRejectionLocationRepository
     * @param ReasonForRejectionTypeRepositoryInterface $reasonForRejectionTypeRepository
     * @param BrakeTestResultsHelper $brakeTestResultsHelper
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        MotTestValidator $motTestValidator,
        ApiPerformMotTestAssertion $performMotTestAssertion,
        MotTestRepository $motTestRepository,
        RfrRepository $rfrRepository,
        MotTestReasonForRejectionRepository $motTestReasonForRejectionRepository,
        MotTestReasonForRejectionLocationRepository $motTestReasonForRejectionLocationRepository,
        ReasonForRejectionTypeRepositoryInterface $reasonForRejectionTypeRepository,
        BrakeTestResultsHelper $brakeTestResultsHelper
    ) {
        $this->entityManager = $entityManager;
        $this->authService = $authService;
        $this->motTestValidator = $motTestValidator;
        $this->performMotTestAssertion = $performMotTestAssertion;
        $this->motTestRepository = $motTestRepository;
        $this->rfrRepository = $rfrRepository;
        $this->motTestReasonForRejectionRepository = $motTestReasonForRejectionRepository;
        $this->motTestReasonForRejectionLocationRepository = $motTestReasonForRejectionLocationRepository;
        $this->reasonForRejectionTypeRepository = $reasonForRejectionTypeRepository;
        $this->brakeTestResultsHelper = $brakeTestResultsHelper;
    }

    /**
     * @param int $defectId
     *
     * @throws NotFoundException if the ReasonForRejection entity is not found in the database
     *
     * @return ReasonForRejection
     */
    public function getDefect($defectId) : ReasonForRejection
    {
        $defect = $this->rfrRepository->get($defectId);

        if (!$defect) {
            throw new NotFoundException('Defect', $defectId);
        }

        return $defect;
    }

    /**
     * @param MotTest $motTest
     * @param $data
     * @return MotTestReasonForRejection
     * @throws BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function addReasonForRejection(MotTest $motTest, $data)
    {
        $this->performMotTestAssertion->assertGranted($motTest);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        $rfr = $this->createRfrFromData($data, $motTest);

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($rfr);
        }

        if ($this->isBrakePerformanceNotTestedRfr($rfr)) {
            $this->clearBrakeTestResults($motTest);
        }

        if ($this->motTestValidator->validateMotTestReasonForRejection($rfr)) {
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

        return $rfr;
    }

    /**
     * @param int $motTestRfrId
     * @param $data
     *
     * @throws BadRequestException
     * @throws DataValidationException
     * @throws NotFoundException
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function editReasonForRejection(int $motTestRfrId, $data)
    {
        /** @var MotTestReasonForRejection $rfr */
        $rfr = $this->motTestReasonForRejectionRepository->find($motTestRfrId);

        $motTest = $rfr->getMotTest();

        if ($this->isTrainingTest($motTest)) {
            $this->authService->assertGranted(PermissionInSystem::MOT_DEMO_TEST_PERFORM);
        } else {
            $this->authService->assertGranted(PermissionInSystem::MOT_TEST_PERFORM);
        }

        $this->motTestValidator->assertCanBeUpdated($motTest);

        $locationLateral = ArrayUtils::tryGet($data, 'locationLateral');
        $locationLongitudinal = ArrayUtils::tryGet($data, 'locationLongitudinal');
        $locationVertical = ArrayUtils::tryGet($data, 'locationVertical');
        $comment = ArrayUtils::tryGet($data, 'comment');

        $rfrTypeName = $rfr->getType()->getReasonForRejectionType();

        $rfr->setLocation($this->fetchLocation($locationLateral, $locationLongitudinal, $locationVertical))
            ->setFailureDangerous($this->getIsFailureDangerous($data, $rfr->getReasonForRejection(), $rfrTypeName))
            ->getMotTestReasonForRejectionComment()->setComment($comment);

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($rfr);
        }

        if ($this->motTestValidator->validateMotTestReasonForRejection($rfr)) {
            $this->motTestReasonForRejectionRepository->save($rfr);
        }
    }

    /**
     * @param array   $data
     * @param MotTest $motTest
     *
     * @throws NotFoundException
     * @throws RequiredFieldException
     *
     * @return MotTestReasonForRejection
     */
    public function createRfrFromData($data, MotTest $motTest) : MotTestReasonForRejection
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty([self::RFR_ID_FIELD, self::TYPE_FIELD], $data);

        $rfrId = ($data[self::RFR_ID_FIELD] > 0 ? $data[self::RFR_ID_FIELD] : null);
        $comment = ArrayUtils::tryGet($data, self::COMMENT_FIELD);

        $motTestRfr = (new MotTestReasonForRejection())
            ->setMotTest($motTest)
            ->setGenerated(ArrayUtils::tryGet($data, self::GENERATED_FIELD, false));

        if (!is_null($comment)) {
            $motTestRfr->setMotTestReasonForRejectionComment(
                (new MotTestReasonForRejectionComment())->setComment($comment));
        }

        if ($rfrId === null) {
            // "Custom description" field is capped to 100 characters.
            $motTestRfr->setCustomDescription(
                (new MotTestReasonForRejectionDescription())
                    ->setCustomDescription(substr($comment, 0, 100)));
        }

        $motTestRfr->setLocation($this->fetchLocation(ArrayUtils::tryGet($data, self::LATERAL_LOCATION_FIELD),
            ArrayUtils::tryGet($data, self::LONGITUDINAL_LOCATION_FIELD),
            ArrayUtils::tryGet($data, self::VERRTICAL_LOCATION_FIELD)));

        /** @var ReasonForRejection $reasonForRejection */
        $reasonForRejection = $this->getReasonForRejection($rfrId);

        $motTestRfr->setReasonForRejection($reasonForRejection);

        /** @var ReasonForRejectionType $rfrType */
        $rfrType = $this->getRfrType($data[self::TYPE_FIELD], $reasonForRejection);
        $motTestRfr->setType($rfrType);
        $motTestRfr->setFailureDangerous(
            $this->getIsFailureDangerous($data, $reasonForRejection, $rfrType->getReasonForRejectionType())
        );

        return $motTestRfr;
    }

    /**
     * @param $rfrId
     *
     * @return ReasonForRejection|null
     *
     * @throws NotFoundException
     */
    private function getReasonForRejection($rfrId)
    {
        if ($rfrId !== null) {
            /** @var ReasonForRejection $reasonForRejection */
            $reasonForRejection = $this->rfrRepository->get($rfrId);

            if (!$reasonForRejection) {
                throw new NotFoundException('Reason for Rejection', $rfrId);
            }

            return $reasonForRejection;
        }

        return null;
    }

    /**
     * @param string $typeName
     * @param ReasonForRejection $reasonForRejection
     * @return ReasonForRejectionType
     */
    private function getRfrType(string $typeName, $reasonForRejection) : ReasonForRejectionType
    {
        $rfrTypeToFind = $typeName;
        if ($reasonForRejection !== null
            && $typeName == ReasonForRejectionTypeName::FAIL
            && $reasonForRejection->getRfrDeficiencyCategory()->getCode() == RfrDeficiencyCategoryCode::MINOR) {
            $rfrTypeToFind = ReasonForRejectionTypeName::ADVISORY;
        }

        return $this->reasonForRejectionTypeRepository
            ->getByType($rfrTypeToFind);
    }

    /**
     * @param $data
     * @param ReasonForRejection $reasonForRejection
     * @param string             $rfrTypeName
     *
     * @return bool|mixed
     *
     * @throws DataValidationException
     */
    private function getIsFailureDangerous($data, $reasonForRejection, $rfrTypeName)
    {
        $dangerousFlagFromPostData = ArrayUtils::tryGet($data, self::FAILURE_DANGEROUS_FIELD, false);
        if ($reasonForRejection !== null) {
            if (!$reasonForRejection->isPreEuDirective() && $rfrTypeName == ReasonForRejectionTypeName::ADVISORY) {
                return false;
            }

            $deficiencyCategoryCode = $reasonForRejection->getRfrDeficiencyCategory()->getCode();

            if ($deficiencyCategoryCode == RfrDeficiencyCategoryCode::DANGEROUS) {
                return true;
            }
            if ($deficiencyCategoryCode == RfrDeficiencyCategoryCode::MAJOR ||
                $deficiencyCategoryCode == RfrDeficiencyCategoryCode::MINOR) {
                if ($dangerousFlagFromPostData == true) {
                    throw new DataValidationException();
                }

                return false;
            }
        }

        return $dangerousFlagFromPostData;
    }

    /**
     * @param int $motTestNumber
     * @param int $motTestRfrId
     *
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function deleteReasonForRejectionById($motTestNumber, $motTestRfrId)
    {
        /** @var MotTestReasonForRejection $rfrToDelete */
        $rfrToDelete = $this->motTestReasonForRejectionRepository->find($motTestRfrId);

        if (!$rfrToDelete instanceof MotTestReasonForRejection) {
            throw new NotFoundException(sprintf('Unable to fetch an MotTestReasonForRejection with ID "%s"',
                $motTestRfrId));
        }
        $this->assertRfrCanBeRemovedOrRepaired($motTestNumber, $rfrToDelete);

        $this->removeReasonForRejection($rfrToDelete);
        $this->entityManager->flush();
    }

    /**
     * @param $rfrToDelete
     */
    public function removeReasonForRejection($rfrToDelete)
    {
        $this->entityManager->remove($rfrToDelete);
    }

    /**
     * @param int $motTestNumber
     * @param int $motTestRfrId
     *
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function markReasonForRejectionAsRepaired($motTestNumber, $motTestRfrId)
    {
        foreach (['motTestNumber' => $motTestNumber, 'motTestRfrId' => $motTestRfrId] as $name => $value) {
            if (!is_int($value) || $value <= 0) {
                throw new BadRequestException(sprintf('Field "%s" is not valid: "%s"', $name, $value),
                    BadRequestException::ERROR_CODE_INVALID_DATA);
            }
            unset($name, $value);
        }

        /** @var MotTestReasonForRejection $motTestRfr */
        $motTestRfr = $this->motTestReasonForRejectionRepository->find($motTestRfrId);
        if (!$motTestRfr instanceof MotTestReasonForRejection) {
            throw new NotFoundException('MotTestReasonForRejection', sprintf('id: %d'.$motTestRfrId));
        }
        $this->assertRfrCanBeRemovedOrRepaired($motTestNumber, $motTestRfr);

        $this->createReasonForRejectionMarkedAsRepairedRecord($motTestRfr);
        $this->entityManager->flush();
    }

    /**
     * @param int $motTestNumber
     * @param int $motTestRfrId
     *
     * @throws BadRequestException
     * @throws NotFoundException
     */
    public function undoMarkReasonForRejectionAsRepaired($motTestNumber, $motTestRfrId)
    {
        foreach (['motTestNumber' => $motTestNumber, 'motTestRfrId' => $motTestRfrId] as $name => $value) {
            if (!is_int($value) || $value <= 0) {
                throw new BadRequestException(sprintf('Field "%s" is not valid: "%s"', $name, $value),
                    BadRequestException::ERROR_CODE_INVALID_DATA);
            }
            unset($name, $value);
        }

        /** @var MotTestReasonForRejection $motTestRfr */
        $motTestRfr = $this->motTestReasonForRejectionRepository->find($motTestRfrId);

        if (!$motTestRfr instanceof MotTestReasonForRejection) {
            throw new NotFoundException(sprintf('Unable to fetch an MotTestReasonForRejection with ID "%s"',
                $motTestRfrId));
        }
        $this->assertRfrCanBeRemovedOrRepaired($motTestNumber, $motTestRfr);

        if ($this->isBrakePerformanceNotTestedRfr($motTestRfr)) {
            $this->clearBrakeTestResults($this->getMotTestFromMotTestNumber($motTestNumber));
        }

        $this->removeReasonForRejectionMarkedAsRepairedRecord($motTestRfr);
        $this->entityManager->flush();
    }

    /**
     * @param MotTestReasonForRejection $rfrToRepair
     */
    public function createReasonForRejectionMarkedAsRepairedRecord($rfrToRepair)
    {
        $motTestRfrMarkedAsRepaired = new MotTestReasonForRejectionMarkedAsRepaired($rfrToRepair);

        $this->entityManager->persist($motTestRfrMarkedAsRepaired);
        $this->entityManager->flush();
    }

    /**
     * @param MotTestReasonForRejection $motTestRfr
     *
     * @internal param int $motTestRfrId
     */
    private function removeReasonForRejectionMarkedAsRepairedRecord(MotTestReasonForRejection $motTestRfr)
    {
        $motTestRfr->undoMarkedAsRepaired();
        $this->entityManager->flush();
    }

    /**
     * @param int                       $motTestNumber
     * @param MotTestReasonForRejection $motTestRfr
     *
     * @throws BadRequestException
     * @throws NotFoundException
     */
    private function assertRfrCanBeRemovedOrRepaired($motTestNumber, MotTestReasonForRejection $motTestRfr)
    {
        $this->performMotTestAssertion->assertGranted($motTestRfr->getMotTest());
        $this->motTestValidator->assertCanBeUpdated($motTestRfr->getMotTest());

        $motTest = $motTestRfr->getMotTest();

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($motTestRfr);
        }

        if (!$motTestRfr->getCanBeDeleted()) {
            throw new BadRequestException('This Reason for Rejection type cannot be removed or repaired',
                BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        if ($motTestRfr->getMotTest()->getNumber() !== (string) $motTestNumber) {
            throw new NotFoundException('Match for Reason for Rejection on Selected Mot Test');
        }
    }

    /**
     * @param MotTestReasonForRejection $motTestRfr
     */
    private function checkPermissionsForRfr(MotTestReasonForRejection $motTestRfr)
    {
        // Added null check until null check is resolved in createRfrFromData
        if ($motTestRfr->getReasonForRejection() !== null) {

            $reasonForRejection = $this->rfrRepository->get(
                $motTestRfr->getReasonForRejection()->getRfrId()
            );

            if ($reasonForRejection->isForVehicleExaminerOnly()) {
                $this->authService->assertGranted(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED);
            } elseif ($reasonForRejection->isForTesterOnly()) {
                $this->authService->assertGranted(PermissionInSystem::TESTER_RFR_ITEMS_NOT_TESTED);
            }
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    private function isTrainingTest(MotTest $motTest)
    {
        $testTypeCode = $motTest->getMotTestType()->getCode();

        return $testTypeCode == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING;
    }

    /**
     * @param string $lateral
     * @param string $longitudinal
     * @param string $vertical
     *
     * @return MotTestReasonForRejectionLocation
     */
    private function fetchLocation($lateral, $longitudinal, $vertical)
    {
        $location = $this->motTestReasonForRejectionLocationRepository
            ->getLocation($lateral, $longitudinal, $vertical);

        if (!$location) {
            $location = new MotTestReasonForRejectionLocation();
            $location->setLateral($lateral)
                ->setLongitudinal($longitudinal)
                ->setVertical($vertical);
        }

        return $location;
    }

    /**
     * @param MotTestReasonForRejection $motTestRfr
     *
     * @return bool
     */
    private function isBrakePerformanceNotTestedRfr(MotTestReasonForRejection $motTestRfr)
    {
        if ($motTestRfr === null) {
            return false;
        }
        $reasonForRejection = $motTestRfr->getReasonForRejection();
        if ($reasonForRejection === null) {
            return false;
        }
        $rfrId = $reasonForRejection->getRfrId();

        return $this->isBrakePerformanceNotTestedRfrById($rfrId);
    }

    /**
     * @param int $motTestRfrId
     *
     * @return bool
     */
    private function isBrakePerformanceNotTestedRfrById($motTestRfrId)
    {
        if ($motTestRfrId === null) {
            return false;
        }

        return (in_array($motTestRfrId, ReasonForRejectionConstants::BRAKE_PERFORMANCE_NOT_TESTED_RFR_IDS));
    }

    /**
     * @param MotTest $motTest
     */
    private function clearBrakeTestResults(MotTest $motTest)
    {
        $this->brakeTestResultsHelper->deleteAllBrakeTestResults($motTest);
        $this->deleteAllGeneratedRfrs($motTest);
    }

    /**
     * @param MotTest $motTest
     */
    private function deleteAllGeneratedRfrs(MotTest $motTest)
    {
        $generatedRfrs = $this->motTestReasonForRejectionRepository->findBy(['generated' => true, 'motTestId' => $motTest->getId()]);
        foreach ($generatedRfrs as $rfr) {
            $this->removeReasonForRejection($rfr);
        }
        $this->entityManager->flush();
    }

    /**
     * @param int $motTestNumber
     *
     * @return MotTest
     */
    private function getMotTestFromMotTestNumber($motTestNumber)
    {
        $motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);

        return $motTest;
    }
}
