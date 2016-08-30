<?php

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
use DvsaEntities\Entity\ReasonForRejection;
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

    /** @var MotTestValidator $motTestValidator */
    protected $motTestValidator;

    /** @var AuthorisationServiceInterface $authService */
    protected $authService;

    /** @var TestItemSelectorService $testItemSelectorService */
    protected $testItemSelectorService;

    /** @var ApiPerformMotTestAssertion $performMotTestAssertion  */
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
     * @throws NotFoundException If the ReasonForRejection entity is not found in the database.
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
            throw new NotFoundException("Defect", $defectId);
        }

        return $defect;
    }

    public function addReasonForRejection(MotTest $motTest, $data)
    {
        $this->performMotTestAssertion->assertGranted($motTest);
        $this->motTestValidator->assertCanBeUpdated($motTest);

        $rfr = $this->createRfrFromData($data, $motTest);

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($rfr);
        }

        if ($this->motTestValidator->validateMotTestReasonForRejection($rfr)) {
            $this->entityManager->persist($rfr);
            $this->entityManager->flush();

            return $rfr->getId();
        }

        return $rfr->getId();
    }

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

        $rfr->setLocationLateral($locationLateral)
            ->setLocationLongitudinal($locationLongitudinal)
            ->setLocationVertical($locationVertical)
            ->setComment($comment)
            ->setFailureDangerous($failureDangerous);

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

        $motTestRfr = new MotTestReasonForRejection();
        $motTestRfr->setMotTest($motTest)
            ->setType($type)
            ->setLocationLateral($locationLateral)
            ->setLocationLongitudinal($locationLongitudinal)
            ->setLocationVertical($locationVertical)
            ->setComment($comment)
            ->setFailureDangerous($failureDangerous)
            ->setGenerated($generated);

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
            $motTestRfr->setCustomDescription($customDescription);
        }

        return $motTestRfr;
    }

    public function deleteReasonForRejectionById($motTestNumber, $motTestRfrId)
    {
        /** @var MotTestReasonForRejection $rfrToDelete */
        $rfrToDelete = $this->entityManager->find(MotTestReasonForRejection::class, $motTestRfrId);
        if (!$rfrToDelete) {
            throw new NotFoundException('Reason for Rejection entry');
        }

        $this->performMotTestAssertion->assertGranted($rfrToDelete->getMotTest());
        $this->motTestValidator->assertCanBeUpdated($rfrToDelete->getMotTest());

        $motTest = $rfrToDelete->getMotTest();

        if (!$this->isTrainingTest($motTest)) {
            $this->checkPermissionsForRfr($rfrToDelete);
        }

        if ($rfrToDelete->getMotTest()->getNumber() !== (string) $motTestNumber) {
            throw new NotFoundException('Match for Reason for Rejection on Selected Mot Test');
        }

        if ($rfrToDelete->getCanBeDeleted()) {
            $this->removeReasonForRejection($rfrToDelete);
        } else {
            throw new BadRequestException(
                'This Reason for Rejection type cannot be deleted', BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }

        $this->entityManager->flush();
    }

    public function removeReasonForRejection($rfrToDelete)
    {
        $this->entityManager->remove($rfrToDelete);
    }

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

    private function isTrainingTest(MotTest $motTest)
    {
        $testTypeCode = $motTest->getMotTestType()->getCode();

        return $testTypeCode == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING ? true : false;
    }
}
