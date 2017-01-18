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
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionComment;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
use DvsaEntities\Entity\MotTestReasonForRejectionLocation;
use DvsaEntities\Entity\MotTestReasonForRejectionMarkedAsRepaired;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Service\Validator\MotTestValidator;

/**
 * Class MotTestReasonForRejectionService.
 */
class MotTestReasonForRejectionService extends AbstractService
{
    const RFR_ID_FIELD = 'rfrId';
    const TYPE_FIELD = 'type';
    const LONGITUDINAL_LOCATION_FIELD = 'locationLongitudinal';
    const COMMENT_FIELD = 'comment';

    /**
     * @var MotTestValidator
     */
    protected $motTestValidator;

    /**
     * @var AuthorisationServiceInterface
     */
    protected $authService;

    /**
     * @var TestItemSelectorService
     */
    protected $testItemSelectorService;

    /**
     * @var ApiPerformMotTestAssertion
     */
    private $performMotTestAssertion;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /**
     * MotTestReasonForRejectionService constructor.
     *
     * @param EntityManager                 $entityManager
     * @param AuthorisationServiceInterface $authService
     * @param MotTestValidator              $motTestValidator
     * @param TestItemSelectorService       $motTestItemSelectorService
     * @param ApiPerformMotTestAssertion    $performMotTestAssertion
     * @param FeatureToggles                $featureToggles
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        MotTestValidator $motTestValidator,
        TestItemSelectorService $motTestItemSelectorService,
        ApiPerformMotTestAssertion $performMotTestAssertion,
        FeatureToggles $featureToggles
    ) {
        parent::__construct($entityManager);

        $this->authService = $authService;
        $this->motTestValidator = $motTestValidator;
        $this->testItemSelectorService = $motTestItemSelectorService;
        $this->performMotTestAssertion = $performMotTestAssertion;
        $this->featureToggles = $featureToggles;
    }

    /**
     * @param int $defectId
     *
     * @throws NotFoundException If the ReasonForRejection entity is not found in the database
     *
     * @return ReasonForRejection
     */
    public function getDefect($defectId)
    {
        /* @var ReasonForRejection $reasonForRejection */
        $defect = $this
            ->entityManager
            ->getRepository(ReasonForRejection::class)
            ->find($defectId);

        if (!$defect) {
            throw new NotFoundException('Defect', $defectId);
        }

        return $defect;
    }

    /**
     * @param MotTest $motTest
     * @param $data
     *
     * @return int
     */
    public function addReasonForRejection(MotTest $motTest, $data)
    {
        $this->performMotTestAssertion->assertGranted($motTest);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        $rfr = $this->createRfrFromData($data, $motTest);

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($rfr);
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

        return $rfr->getId();
    }

    /**
     * @param $motTestRfrId
     * @param $data
     *
     * @return bool|null
     */
    public function editReasonForRejection($motTestRfrId, $data)
    {
        /** @var MotTestReasonForRejection $rfr */
        $rfr = $this->entityManager->find(MotTestReasonForRejection::class, $motTestRfrId);

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
        $failureDangerous = ArrayUtils::tryGet($data, 'failureDangerous', false);

        $location = $this->fetchLocation($locationLateral, $locationLongitudinal,$locationVertical);

        $rfr->setLocation($location)
            ->setFailureDangerous($failureDangerous)
            ->getMotTestReasonForRejectionComment()->setComment($comment);

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($rfr);
        }

        if ($this->motTestValidator->validateMotTestReasonForRejection($rfr)) {
            $this->entityManager->persist($rfr);
            $this->entityManager->flush();

            return true;
        }

        return null;
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
    public function createRfrFromData($data, MotTest $motTest)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty([self::RFR_ID_FIELD, self::TYPE_FIELD], $data);

        $rfrId = ($data[self::RFR_ID_FIELD] > 0 ? $data[self::RFR_ID_FIELD] : null);
        $type = $data[self::TYPE_FIELD];

        $locationLateral = ArrayUtils::tryGet($data, 'locationLateral');
        $locationLongitudinal = ArrayUtils::tryGet($data, 'locationLongitudinal');
        $locationVertical = ArrayUtils::tryGet($data, 'locationVertical');
        $comment = ArrayUtils::tryGet($data, 'comment');
        $failureDangerous = ArrayUtils::tryGet($data, 'failureDangerous', false);
        $generated = ArrayUtils::tryGet($data, 'generated', false);

        $location = $this->fetchLocation($locationLateral, $locationLongitudinal,$locationVertical);

        $rfrType = $this->getEntityManager()->getRepository(ReasonForRejectionType::class)->findOneBy(
            ['reasonForRejectionType' => $type]
        );

        $motTestRfr = new MotTestReasonForRejection();
        $motTestRfr->setMotTest($motTest)
            ->setType($rfrType)
            ->setLocation($location)
            ->setFailureDangerous($failureDangerous)
            ->setGenerated($generated);

        if (!is_null($comment)) {
            $motTestRfr->setMotTestReasonForRejectionComment(
                (new MotTestReasonForRejectionComment())->setComment($comment)
            );
        }

        // this will be removed in future, when db schema is updated...
        if ($rfrId !== null) {
            /** @var ReasonForRejection $reasonForRejection */
            $reasonForRejection = $this->entityManager->find(ReasonForRejection::class, ['rfrId' => $rfrId]);

            if (!$reasonForRejection) {
                throw new NotFoundException('Reason for Rejection', $rfrId);
            }
            $motTestRfr->setReasonForRejection($reasonForRejection);
        } else {
            // "Custom description" field is capped to 100 characters.
            $customDescription = (true === $this->featureToggles->isEnabled(FeatureToggle::TEST_RESULT_ENTRY_IMPROVEMENTS))
                ? substr($comment, 0, 100) : $comment;

            $description = new MotTestReasonForRejectionDescription();
            $description->setCustomDescription($customDescription);

            $motTestRfr->setCustomDescription($description);
        }

        return $motTestRfr;
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
        $rfrToDelete = $this->entityManager->find(MotTestReasonForRejection::class, $motTestRfrId);
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
        $motTestRfr = $this->entityManager->getRepository(MotTestReasonForRejection::class)->find($motTestRfrId);
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
        $motTestRfr = $this->entityManager->getRepository(MotTestReasonForRejection::class)->find($motTestRfrId);
        if (!$motTestRfr instanceof MotTestReasonForRejection) {
            throw new NotFoundException(sprintf('Unable to fetch an MotTestReasonForRejection with ID "%s"',
                $motTestRfrId));
        }
        $this->assertRfrCanBeRemovedOrRepaired($motTestNumber, $motTestRfr);

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
            $reasonForRejection = $this->testItemSelectorService->getReasonForRejectionById(
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

        return $testTypeCode == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING ? true : false;
    }

    /**
     * @param string $lateral
     * @param string $longitudinal
     * @param string $vertical
     * @return MotTestReasonForRejectionLocation
     */
    private function fetchLocation($lateral, $longitudinal, $vertical)
    {
        $location = $this->getEntityManager()->getRepository(MotTestReasonForRejectionLocation::class)->getLocation(
            $lateral, $longitudinal, $vertical
        );

        if (!$location) {

            $location = new MotTestReasonForRejectionLocation();
            $location->setLateral($lateral)
                ->setLongitudinal($longitudinal)
                ->setVertical($vertical);
        }

        return $location;
    }
}
