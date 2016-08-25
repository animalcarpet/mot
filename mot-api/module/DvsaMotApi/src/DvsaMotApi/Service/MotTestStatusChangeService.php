<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\AbandonVehicleTestAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Constants\Network;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Time;
use DvsaCommon\Domain\MotTestType as MotTestTypeConst;
use DvsaCommon\Enum\EnfRetestModeId;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaAuthentication\Service\Exception\OtpException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\BrakeTestResult;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\SiteTestingDailySchedule;
use DvsaEntities\Repository\EnforcementFullPartialRetestRepository;
use DvsaEntities\Repository\MotTestReasonForCancelRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\MotTestStatusRepository;
use DvsaMotApi\Generator\MotTestNumberGenerator;
use DvsaMotApi\Service\Helper\MotTestCloneHelper;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\Validator\MotTestStatusChangeValidator;
use DvsaMotApi\Service\Validator\MotTestValidator;
use OrganisationApi\Service\OrganisationService;

/**
 * Class MotTestStatusChangeService
 *
 * @package DvsaMotApi\Service
 */
class MotTestStatusChangeService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    const FIELD_STATUS = 'status';
    const FIELD_FULL_PARTIAL_RETEST = 'fullPartialRetest';
    const FIELD_PARTIAL_REASONS = 'partialReasons';
    const FIELD_ITEMS_NOT_TESTED = 'partialItemsMissed';
    const FIELD_CANCEL_COMMENT = 'cancelComment';
    const FIELD_OTP = 'oneTimePassword';
    const FIELD_REASON_FOR_CANCEL = 'reasonForCancelId';
    const FIELD_REASON_FOR_ABORT = 'reasonForAbort';
    const FIELD_CLIENT_IP = 'clientIp';

    private static $MOT_STATUS_REQUIRE_OTP
        = [
            MotTestStatusName::FAILED,
            MotTestStatusName::PASSED,
            MotTestStatusName::ABANDONED
        ];

    private static $ABORT_STATUSES
        = [
            MotTestStatusName::ABORTED,
            MotTestStatusName::ABORTED_VE
        ];

    private static $MOT_STATUS_REQUIRING_SLOT_RETURN
        = [
            MotTestStatusName::FAILED,
            MotTestStatusName::ABORTED,
            MotTestStatusName::ABORTED_VE,
            MotTestStatusName::ABANDONED
        ];

    private static $MOT_TEST_COMPLETED_STATUSES
        = [
            MotTestStatusName::FAILED,
            MotTestStatusName::PASSED,
        ];

    private static $VEHICLE_WEIGHT_FROM_VSI_VEHICLE_CLASSES
        = [
            VehicleClassCode::CLASS_3,
            VehicleClassCode::CLASS_4,
        ];

    private static $VEHICLE_WEIGHT_FROM_DGW_VEHICLE_CLASSES
        = [
            VehicleClassCode::CLASS_5,
            VehicleClassCode::CLASS_7,
        ];

    /** @var MotTestMapper $motTestMapper */
    protected $motTestMapper;
    /** @var  MotTestDateHelperService */
    protected $motTestDateHelper;
    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;
    /** @var AuthorisationServiceInterface $authService */
    private $authService;
    /** @var MotTestValidator $motTestValidator */
    private $motTestValidator;
    /** @var MotTestStatusChangeValidator $motTestStatusChangeValidator */
    private $motTestStatusChangeValidator;
    /** @var DateTimeHolder $dateTimeHolder */
    private $dateTimeHolder;
    /** @var OtpService $otpService */
    private $otpService;
    /** @var MotTestReasonForCancelRepository */
    private $reasonForCancelRepository;
    /** @var  MotTestRepository $motTestRepository */
    private $motTestRepository;
    /** @var  OrganisationService $organisationService */
    private $organisationService;
    /** @var  EnforcementFullPartialRetestRepository $fullPartialRetestRepository */
    private $fullPartialRetestRepository;
    /** @var  TestingOutsideOpeningHoursNotificationService $outsideHoursNotificationService */
    private $outsideHoursNotificationService;
    /** @var MotIdentityProviderInterface */
    private $motIdentityProvider;
    /** @var ApiPerformMotTestAssertion */
    private $performMotTestAssertion;
    /** @var \DvsaCommonApi\Filter\XssFilter */
    protected $xssFilter;

    /**
     * @param \DvsaAuthorisation\Service\AuthorisationServiceInterface $authService
     * @param \DvsaMotApi\Service\Validator\MotTestValidator $motTestValidator
     * @param \DvsaMotApi\Service\Validator\MotTestStatusChangeValidator $motTestStatusChangeValidator
     * @param \dvsaAuthentication\Service\OtpService $otpService
     * @param \OrganisationApi\Service\OrganisationService $organisationService
     * @param \DvsaMotApi\Service\Mapper\MotTestMapper $motTestMapper
     * @param \DvsaEntities\Repository\MotTestRepository $motTestRepository
     * @param \DvsaEntities\Repository\MotTestReasonForCancelRepository $reasonForCancelRepository
     * @param \DvsaEntities\Repository\EnforcementFullPartialRetestRepository $fullPartialRetestRepository
     * @param \DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService $outsideHoursNotificationService
     * @param \DvsaMotApi\Service\MotTestDateHelperService $motTestDateHelper
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \DvsaCommon\Auth\MotIdentityProviderInterface $motIdentityProvider
     * @param \DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion $performMotTestAssertion
     * @param \DvsaCommonApi\Filter\XssFilter $xssFilter
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        MotTestValidator $motTestValidator,
        MotTestStatusChangeValidator $motTestStatusChangeValidator,
        OtpService $otpService,
        OrganisationService $organisationService,
        MotTestMapper $motTestMapper,
        MotTestRepository $motTestRepository,
        MotTestReasonForCancelRepository $reasonForCancelRepository,
        EnforcementFullPartialRetestRepository $fullPartialRetestRepository,
        TestingOutsideOpeningHoursNotificationService $outsideHoursNotificationService,
        MotTestDateHelperService $motTestDateHelper,
        EntityManager $entityManager,
        MotIdentityProviderInterface $motIdentityProvider,
        ApiPerformMotTestAssertion $performMotTestAssertion,
        XssFilter $xssFilter
    ) {
        $this->authService = $authService;
        $this->motTestValidator = $motTestValidator;
        $this->motTestStatusChangeValidator = $motTestStatusChangeValidator;
        $this->dateTimeHolder = new DateTimeHolder();
        $this->otpService = $otpService;
        $this->outsideHoursNotificationService = $outsideHoursNotificationService;
        $this->organisationService = $organisationService;
        $this->motTestMapper = $motTestMapper;
        $this->motTestRepository = $motTestRepository;
        $this->reasonForCancelRepository = $reasonForCancelRepository;
        $this->fullPartialRetestRepository = $fullPartialRetestRepository;
        $this->motTestDateHelper = $motTestDateHelper;
        $this->entityManager = $entityManager;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->performMotTestAssertion = $performMotTestAssertion;
        $this->xssFilter = $xssFilter;
    }

    /**
     * Update MOT test status
     *
     * @param string $motTestNumber
     * @param array  $data
     *
     * @return array containing (1) array mot test data
     *                          (2) whether a slot should be returned to the owning organisation
     * @throws ForbiddenException
     * @throws OtpException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \Exception
     */
    public function updateStatus($motTestNumber, $data)
    {
        $motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);

        // Record IP address on completion
        $clientIp = ArrayUtils::tryGet($data, self::FIELD_CLIENT_IP, Network::DEFAULT_CLIENT_IP);
        $motTest->setClientIp($clientIp);

        if (isset($data['cancelComment'])) {
            $data['cancelComment'] = $this->xssFilter->filter($data['cancelComment']);
        }
        $this->motTestStatusChangeValidator->validateDataForNewStatus($data);
        $reasonForCancelIsAbandoned = $this->isReasonForCancelAbandoned(
            $data
        );

        if ($reasonForCancelIsAbandoned) {
            $this->motTestStatusChangeValidator->validateDataForAbandonedMotTest($data);
            $newStatus = MotTestStatusName::ABANDONED;
        } else {
            $newStatus = $data[self::FIELD_STATUS];
        }

        if (!$motTest->isActive()) {
            throw new ForbiddenException(InvalidTestStatus::getMessage($motTest->getStatus()));
        }

        if (in_array($newStatus, self::$ABORT_STATUSES)) {
            $this->motTestValidator->assertCanBeAborted($motTest);
        } elseif (MotTestStatusName::ABANDONED === $newStatus) {
            $this->assertCanAbandonVehicle($motTest);
        } else {
            $this->assertCanConfirmMotTest($motTest, $newStatus);
            $this->performMotTestAssertion->assertGranted($motTest);
        }

        $otpToken = ArrayUtils::tryGet($data, self::FIELD_OTP);
        $otpIsApplicable = $this->isOtpApplicable($newStatus, $motTest->getMotTestType());

        if ($otpIsApplicable) {
            $this->otpService->authenticate($otpToken);
        }

        $this->motTestStatusChangeValidator->verifyThatStatusTransitionIsPossible($motTest, $newStatus);

        $this->inTransaction(
            function () use ($motTest, &$data, $newStatus) {
                $this->statusAction($motTest, $data, $newStatus);
                if (array_key_exists(self::FIELD_FULL_PARTIAL_RETEST, $data)) {
                    $this->onFullPartialRetest($motTest, $data);
                }
            }
        );

        $this->entityManager->refresh($motTest); // need to have our entity aware of the after-update trigger

        // NOTE: placed here on purpose to have disjoint transaction
        $this->returnSlotIfApplicable(
            $motTest,
            $newStatus
        );

        return $this->motTestMapper->mapMotTest($motTest);
    }

    private function isReasonForCancelAbandoned($data)
    {
        $status = $data[self::FIELD_STATUS];
        $isAborted = $status === MotTestStatusName::ABORTED;

        if ($isAborted) {
            $reasonForCancelId = $data[self::FIELD_REASON_FOR_CANCEL];
            $reasonForCancel = $this->reasonForCancelRepository->get($reasonForCancelId);

            return $reasonForCancel->getAbandoned();
        }

        return false;
    }

    private function assertCanAbandonVehicle(MotTest $motTest)
    {
        $abandonTestAssertion = new AbandonVehicleTestAssertion(
            $this->motIdentityProvider->getIdentity(),
            $this->authService
        );
        $abandonTestAssertion->setVtsId($motTest->getVehicleTestingStation()->getId());
        $abandonTestAssertion->setMotTestTypeCode($motTest->getMotTestType()->getCode());
        $abandonTestAssertion->setTesterId($motTest->getTester()->getId());
        $abandonTestAssertion->assertGranted();
    }

    private function isOtpApplicable($newStatus, MotTestType $testType)
    {
        $statusChangeRequiresOtp = in_array($newStatus, self::$MOT_STATUS_REQUIRE_OTP);
        $cannotIgnoreOtp = $statusChangeRequiresOtp && !$testType->getIsDemo();

        $secondFactorNotRequiredForCurrentIdentity =
            !$this->motIdentityProvider->getIdentity()->isSecondFactorRequired() &&
            !$this->authService->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP);

        return $cannotIgnoreOtp && $secondFactorNotRequiredForCurrentIdentity;
    }

    // NOTE: there should be a cancel status from client point of view that
    // coerces into either aborted or abandoned

    private function statusAction(MotTest $motTest, $data, $newStatus)
    {
        if ($newStatus === MotTestStatusName::ABORTED) {
            $this->onCancelled($motTest, $data, false);
        } elseif ($newStatus === MotTestStatusName::ABANDONED) {
            $this->onCancelled($motTest, $data, true);
        } elseif ($newStatus === MotTestStatusName::ABORTED_VE) {
            $this->onAbortedByVe($motTest, $data);
        } elseif (in_array($newStatus, self::$MOT_TEST_COMPLETED_STATUSES)) {
            // temporary trick, $newStatus should be resolved at the very beginning
            // the problem lies in cycled prs mot test. If PRS exists on a MOT test
            // that has everything in place a new FAILED test should be created
            // or if we did not rely on statuses, a new passed test should be created
            // and be linked to the failed one. Anyway, bidirectional link should be removed.
            $newStatus = $this->onTestCompleted($motTest, $newStatus);
        }

        $motTest->setStatus($this->getMotTestStatus($newStatus));

        //  --  set Issue & Expire Date  --
        $motTest->setIssuedDate($this->motTestDateHelper->getIssuedDate($motTest));
        $motTest->setExpiryDate($this->motTestDateHelper->getExpiryDate($motTest));
    }

    // NOTE: there should be a cancel status from client point of view that
    // coerces into either aborted or abandoned
    private function onCancelled(MotTest $motTest, $data, $isAbandoned)
    {
        $this->motTestStatusChangeValidator->checkMotTestCanBeCancelled($motTest);

        if ($isAbandoned) {
            RequiredFieldException::CheckIfRequiredFieldsNotEmpty([self::FIELD_CANCEL_COMMENT], $data);
            $motTest->setReasonForTerminationComment($data[self::FIELD_CANCEL_COMMENT]);
        }

        $reasonForCancelId = $data[self::FIELD_REASON_FOR_CANCEL];
        $reasonForCancel = $this->reasonForCancelRepository->get($reasonForCancelId);

        $motTest->setMotTestReasonForCancel($reasonForCancel);

        $this->setMotTestCompletedDate($motTest);
    }

    private function onAbortedByVe(MotTest $motTest, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::VE_MOT_TEST_ABORT);
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty([self::FIELD_REASON_FOR_ABORT], $data);
        $reasonForAbort = $data[self::FIELD_REASON_FOR_ABORT];
        $this->motTestStatusChangeValidator->checkMotTestCanBeAbortedByVe($motTest);
        $motTest->setReasonForTerminationComment($reasonForAbort);
        $this->setMotTestCompletedDate($motTest);
    }

    /**
     * @param MotTest $motTest
     * @param string  $newStatus the status passed in to the service
     *
     * @return string an overridden status in the case of PRS on a passed test
     */
    private function onTestCompleted(MotTest $motTest, $newStatus)
    {
        $isTestPassed = ($newStatus === MotTestStatusName::PASSED);

        $this->setMotTestCompletedDate($motTest);

        //  --  create ReasonForRejection RRS MOT test clone    --
        if (
            $isTestPassed
            && $motTest->hasRfrsOfType(ReasonForRejectionTypeName::PRS)
            && !MotTestTypeConst::isVeAdvisory($motTest->getMotTestType()->getCode())
        ) {
            $newStatus = MotTestStatusName::FAILED;

            $passedMotTest = $this->createPassedMotTestWithoutPrs($motTest);
            $passedMotTest->setPrsMotTest($motTest);

            $newPassedMotTestDate = clone $passedMotTest->getCompletedDate();
            $newPassedMotTestDate->add(new \DateInterval('PT1S'));

            $passedMotTest->setStartedDate($motTest->getCompletedDate());
            $passedMotTest->setCompletedDate($newPassedMotTestDate);

            //  --  set Issue & Expire Date  --
            $passedMotTest->setIssuedDate($this->motTestDateHelper->getIssuedDate($passedMotTest));
            $passedMotTest->setExpiryDate($this->motTestDateHelper->getExpiryDate($passedMotTest));

            $this->motTestRepository->save($passedMotTest);
            $passedMotTest->setNumber(MotTestNumberGenerator::generateMotTestNumber($passedMotTest->getId()));
            $this->motTestRepository->save($passedMotTest);

            $motTest->setPrsMotTest($passedMotTest);
        }

        // -- vehicle weight --
        if ($this->shouldAmendVehicleWeight($motTest)) {
            $brakeTestVehicleWeight = $motTest->getBrakeTestResultClass3AndAbove()->getVehicleWeight();
            if(!$motTest->getMotTestType()->getIsDemo()) {
                $motTest->getVehicle()->setWeight($brakeTestVehicleWeight);
            }
        }

        $this->notifyAboutTestingOutsideHoursIfApplicable($motTest);

        return $newStatus;
    }

    private function createPassedMotTestWithoutPrs(MotTest $motTest)
    {
        $passedMotTest = MotTestCloneHelper::motTestDeepCloneNoCollections($motTest);
        $passedMotTest->setStatus($this->getMotTestStatus(MotTestStatusName::PASSED));
        $this->copyAdvisoryRfrItems($motTest, $passedMotTest);

        return $passedMotTest;
    }

    private function copyAdvisoryRfrItems(MotTest $sourceMotTest, MotTest &$targetMotTest)
    {
        /**
         * @var MotTestReasonForRejection $rfr
         */
        foreach ($sourceMotTest->getMotTestReasonForRejections() as $rfr) {
            if ($rfr->getType() === ReasonForRejectionTypeName::ADVISORY) {
                $cloneRfr = clone $rfr;
                $cloneRfr->setId(null)
                    ->setMotTestId(null)
                    ->setMotTest($targetMotTest);
                $targetMotTest->addMotTestReasonForRejection($cloneRfr);
            }
        }
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    private function shouldAmendVehicleWeight(MotTest $motTest)
    {
        $brakeTestResult = $motTest->getBrakeTestResultClass3AndAbove();
        if (!$brakeTestResult || $brakeTestResult->getVehicleWeight() === null) {
            return false;
        }

        return $this->hasVsiWeight($motTest, $brakeTestResult) || $this->hasDgwWeight($motTest, $brakeTestResult);
    }

    /**
     * @param MotTest         $motTest
     * @param BrakeTestResult $brakeTestResult
     *
     * @return bool
     */
    private function hasVsiWeight(MotTest $motTest, BrakeTestResult $brakeTestResult)
    {
        return in_array($motTest->getVehicleClass()->getCode(), self::$VEHICLE_WEIGHT_FROM_VSI_VEHICLE_CLASSES)
            && $brakeTestResult->getWeightType()->getCode() === WeightSourceCode::VSI;
    }

    /**
     * @param MotTest         $motTest
     * @param BrakeTestResult $brakeTestResult
     *
     * @return bool
     */
    private function hasDgwWeight(MotTest $motTest, BrakeTestResult $brakeTestResult)
    {
        return in_array($motTest->getVehicleClass(), self::$VEHICLE_WEIGHT_FROM_DGW_VEHICLE_CLASSES)
            && $brakeTestResult->getWeightType()->getCode() === WeightSourceCode::DGW;
    }

    /**
     * notify only when performed by a qualifier tester (excl. VE, demo tests, and so on)
     *
     * @param MotTest $motTest
     */
    private function notifyAboutTestingOutsideHoursIfApplicable(MotTest $motTest)
    {
        if ($motTest->getMotTestType()->getIsDemo()) {
            return;
        }

        // notify only when performed by a qualifier tester (excl. VE, demo tests, and so on)
        $schedule = $motTest->getVehicleTestingStation()->getSiteTestingSchedule();

        if ($motTest->getTester()->isQualifiedTester()
            && SiteTestingDailySchedule::isOutsideSchedule(
                Time::fromDateTime(DateUtils::toUserTz($motTest->getCompletedDate())),
                $schedule
            )
        ) {
            $site = $motTest->getVehicleTestingStation();
            $siteBusRoleMapRepository = $this->entityManager->getRepository(
                \DvsaEntities\Entity\SiteBusinessRoleMap::class
            );
            $siteManagerRole = $this->entityManager->getRepository(
                \DvsaEntities\Entity\SiteBusinessRole::class
            )->findOneBy(['code' => SiteBusinessRoleCode::SITE_MANAGER]);
            $roleMap = $siteBusRoleMapRepository->findOneBy(
                ['site' => $site, 'siteBusinessRole' => $siteManagerRole]
            );
            if (!$roleMap) {
                $org = $motTest->getVehicleTestingStation()->getOrganisation();
                /** @var \DvsaEntities\Entity\OrganisationBusinessRoleMap $orgPosRepository */
                $orgBusRoleMapRepository = $this->entityManager->getRepository(
                    \DvsaEntities\Entity\OrganisationBusinessRoleMap::class
                );
                $aedmRole = $this->entityManager->getRepository(
                    \DvsaEntities\Entity\OrganisationBusinessRole::class
                )->findOneBy(['name' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]);
                $roleMap = $orgBusRoleMapRepository->findOneBy(
                    ['organisation' => $org, 'organisationBusinessRole' => $aedmRole]
                );
            }
            $this->outsideHoursNotificationService->notify(
                $motTest->getVehicleTestingStation(),
                $motTest->getTester(),
                $motTest->getCompletedDate(),
                $roleMap->getPerson()
            );
        }
    }

    private function onFullPartialRetest(MotTest $motTest, $data)
    {
        $username = $this->motIdentityProvider->getIdentity()->getUsername();
        $fullPartialRetestId = $data[self::FIELD_FULL_PARTIAL_RETEST];
        $fullPartialRetest = $this->fullPartialRetestRepository->get($fullPartialRetestId);
        $motTest->setFullPartialRetest($fullPartialRetest);

        if (EnfRetestModeId::PARTIAL == $fullPartialRetestId) {
            $partialReinspectionComment = $this->createComment(
                $data[self::FIELD_PARTIAL_REASONS],
                $username
            );
            if ($partialReinspectionComment) {
                $motTest->setPartialReinspectionComment($partialReinspectionComment);
            }
            $itemsNotTestedComment = $this->createComment(
                $data[self::FIELD_ITEMS_NOT_TESTED],
                $username
            );
            if ($itemsNotTestedComment) {
                $motTest->setItemsNotTestedComment($itemsNotTestedComment);
            }
        }
    }

    /**
     * Help function to create and populate a Comment
     *
     * @param string $input
     * @param string $user
     *
     * @return \DvsaEntities\Entity\Comment|null
     *
     */
    private function createComment($input, $user)
    {
        $input = trim($input);
        $comment = null;
        if (strlen($input) > 0) {
            $comment = new Comment();
            $comment->setComment($input);
            $comment->setCommentAuthor($user);
            //$comment->setCreatedBy($user);
        }

        return $comment;
    }

    private function returnSlotIfApplicable(MotTest $motTest, $newStatus)
    {
        if ($motTest->getMotTestType()->getIsDemo()) {
            return null;
        }

        $site = $motTest->getVehicleTestingStation();
        $motTestTypeCode = $motTest->getMotTestType()->getCode();

        $organisation = $site->getOrganisation();

        /** @var \DvsaEntities\Repository\MotTestTypeRepository $motTestTypeRepository */
        $motTestTypeRepository = $this->entityManager
            ->getRepository(\DvsaEntities\Entity\MotTestType::class);

        /** @var MotTestType $motTestType */
        $motTestType = $motTestTypeRepository->findOneByCode($motTestTypeCode);
        if (!$motTestType) {
            throw new \Exception('MotTestType not found by code: ' . $motTestTypeCode);
        }

        if (in_array($newStatus, self::$MOT_STATUS_REQUIRING_SLOT_RETURN)
            && $motTestType->getIsSlotConsuming()
        ) {
            /*
             * Update the slot count in a separate transaction to fix VM-3254
             */
            $this->inTransaction(
                function () use ($organisation) {
                    $this->organisationService->incrementSlotBalance($organisation);
                }
            );
        }
    }

    /**
     * @param \DvsaCommon\Date\DateTimeHolder $dateTimeHolder
     *
     * @return $this
     */
    public function setDateTimeHolder($dateTimeHolder)
    {
        $this->dateTimeHolder = $dateTimeHolder;

        return $this;
    }

    private function assertUserOwnsTheMotTest($motTest)
    {
        if ($this->motIdentityProvider->getIdentity()->getUserId() !== $motTest->getTester()->getId()
        ) {
            throw new UnauthorisedException(
                'This test was started by another user and you are not allowed to confirm its result'
            );
        }
    }

    /**
     * @param \DvsaEntities\Entity\MotTest $motTest
     * @param string                       $newStatus
     *
     * @throws UnauthorisedException
     */
    private function assertCanConfirmMotTest($motTest, $newStatus)
    {
        if ($motTest->getMotTestType()->getIsDemo()) {
            return;
        }

        if (in_array(
            $newStatus,
            [
                MotTestStatusName::FAILED,
                MotTestStatusName::PASSED
            ]
        )
        ) {
            $this->authService->assertGranted(PermissionInSystem::MOT_TEST_CONFIRM);
            $this->assertUserOwnsTheMotTest($motTest);

            $this->authService->assertGrantedAtSite(
                PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE,
                $motTest->getVehicleTestingStation()->getId()
            );
        }
    }

    /**
     * @return MotTestStatusRepository
     */
    private function getMotTestStatusRepository()
    {
        return $this->entityManager->getRepository(MotTestStatus::class);
    }

    /**
     * @param string $name
     * @return MotTestStatus
     */
    private function getMotTestStatus($name)
    {
        return $this->getMotTestStatusRepository()->findByName($name);
    }

    /**
     * @param MotTest $motTest
     */
    private function setMotTestCompletedDate(MotTest $motTest)
    {
        $motTest->setCompletedDate($this->getCompletedDate($motTest));
    }

    /**
     * @param MotTest $motTest
     *
     * @return \DateTime
     */
    private function getCompletedDate(MotTest $motTest)
    {
        if (is_null($motTest->getEmergencyLog())) {
            return $this->dateTimeHolder->getCurrent();
        }

        return $motTest->getStartedDate();
    }
}
