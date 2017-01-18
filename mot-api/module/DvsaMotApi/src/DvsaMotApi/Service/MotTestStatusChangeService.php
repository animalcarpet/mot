<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use DvsaAuthentication\Service\Exception\OtpException;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\Assertion\AbandonVehicleTestAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Network;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Time;
use DvsaCommon\Domain\MotTestType as MotTestTypeConst;
use DvsaCommon\Enum\EnfRetestModeId;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Enum\WeightSourceCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Messages\InvalidTestStatus;
use DvsaCommon\MysteryShopper\MysteryShopperExpiryDateGenerator;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\BrakeTestResult;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestCancelled;
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
 * Class MotTestStatusChangeService.
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

    /**
     * @var array
     */
    private static $MOT_STATUS_REQUIRE_OTP = [
        MotTestStatusName::FAILED,
        MotTestStatusName::PASSED,
        MotTestStatusName::ABANDONED,
    ];

    /**
     * @var array
     */
    private static $ABORT_STATUSES = [
        MotTestStatusName::ABORTED,
        MotTestStatusName::ABORTED_VE,
    ];

    /**
     * @var array
     */
    private static $MOT_STATUS_REQUIRING_SLOT_RETURN
        = [
        MotTestStatusName::FAILED,
        MotTestStatusName::ABORTED,
        MotTestStatusName::ABORTED_VE,
        MotTestStatusName::ABANDONED,
    ];

    /**
     * @var array
     */
    private static $MOT_TEST_COMPLETED_STATUSES = [
        MotTestStatusName::FAILED,
        MotTestStatusName::PASSED,
    ];
    /**
     * @var array
     */
    private static $MOT_TEST_ABORTED_STATUSES = [
        MotTestStatusName::ABORTED,
        MotTestStatusName::ABORTED_VE,
        MotTestStatusName::ABANDONED,
    ];

    /**
     * @var array
     */
    private static $VEHICLE_WEIGHT_FROM_VSI_VEHICLE_CLASSES = [
        VehicleClassCode::CLASS_3,
        VehicleClassCode::CLASS_4,
    ];

    /**
     * @var array
     */
    private static $VEHICLE_WEIGHT_FROM_DGW_VEHICLE_CLASSES = [
        VehicleClassCode::CLASS_5,
        VehicleClassCode::CLASS_7,
    ];

    /**
     * @var MotTestMapper
     */
    protected $motTestMapper;

    /**
     * @var MotTestDateHelperService
     */
    protected $motTestDateHelper;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var MotTestValidator
     */
    private $motTestValidator;

    /**
     * @var MotTestStatusChangeValidator
     */
    private $motTestStatusChangeValidator;

    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;

    /**
     * @var OtpService
     */
    private $otpService;

    /**
     * @var MotTestReasonForCancelRepository
     */
    private $reasonForCancelRepository;

    /**
     * @var MotTestRepository
     */
    private $motTestRepository;

    /**
     * @var OrganisationService
     */
    private $organisationService;

    /**
     * @var EnforcementFullPartialRetestRepository
     */
    private $fullPartialRetestRepository;

    /**
     * @var TestingOutsideOpeningHoursNotificationService
     */
    private $outsideHoursNotificationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $motIdentityProvider;

    /**
     * @var ApiPerformMotTestAssertion
     */
    private $performMotTestAssertion;

    /**
     * @var XssFilter
     */
    protected $xssFilter;

    /**
     * @param AuthorisationServiceInterface                 $authService
     * @param MotTestValidator                              $motTestValidator
     * @param MotTestStatusChangeValidator                  $motTestStatusChangeValidator
     * @param OtpService                                    $otpService
     * @param OrganisationService                           $organisationService
     * @param MotTestMapper                                 $motTestMapper
     * @param MotTestRepository                             $motTestRepository
     * @param MotTestReasonForCancelRepository              $reasonForCancelRepository
     * @param EnforcementFullPartialRetestRepository        $fullPartialRetestRepository
     * @param TestingOutsideOpeningHoursNotificationService $outsideHoursNotificationService
     * @param MotTestDateHelperService                      $motTestDateHelper
     * @param EntityManager                                 $entityManager
     * @param MotIdentityProviderInterface                  $motIdentityProvider
     * @param ApiPerformMotTestAssertion                    $performMotTestAssertion
     * @param XssFilter                                     $xssFilter
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
     * Update MOT test status.
     *
     * @param string $motTestNumber
     * @param array  $data
     *
     * @throws ForbiddenException
     * @throws OtpException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \Exception
     *
     * @return array containing (1) array mot test data
     *               (2) whether a slot should be returned to the owning organisation
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

        // Checking for Site ID is mandatory only for Non-MOT inspection.
        if ($motTest->getMotTestType()->getCode() == MotTestTypeCode::NON_MOT_TEST && !in_array($newStatus, self::$MOT_TEST_ABORTED_STATUSES)) {
            $this->motTestStatusChangeValidator->hasSiteIdBeenEntered($motTest);
            if($motTest->getOrganisation() === null) {
                $motTest->setOrganisation($motTest->getVehicleTestingStation()->getOrganisation());
            }
        }

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
        if ($motTest->getMotTestType()->isNonMotTest()) {
            return;
        }

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

        $this->updateExpiryDateIfMysteryShopper($motTest);
        $motTest->setSubmittedDate(new \DateTime('now'));
    }

    // NOTE: there should be a cancel status from client point of view that
    // coerces into either aborted or abandoned
    private function onCancelled(MotTest $motTest, $data, $isAbandoned)
    {
        $this->motTestStatusChangeValidator->checkMotTestCanBeCancelled($motTest);

        $reasonForCancelId = $data[self::FIELD_REASON_FOR_CANCEL];
        $reasonForCancel = $this->reasonForCancelRepository->get($reasonForCancelId);

        $person = $this->motIdentityProvider->getIdentity()->getPerson();

        $motTestCancelled = new MotTestCancelled();
        $motTestCancelled->setMotTestReasonForCancel($reasonForCancel);

        if ($isAbandoned) {
            RequiredFieldException::CheckIfRequiredFieldsNotEmpty([self::FIELD_CANCEL_COMMENT], $data);

            $comment = new Comment();
            $comment->setComment($data[self::FIELD_CANCEL_COMMENT])
                ->setCommentAuthor($person);
            $motTestCancelled->setComment($comment);
        }

        $motTest->setMotTestCancelled($motTestCancelled);

        // ReasonsForRejection that were flagged as "repaired" should now be permanently removed.
        $this->removeMotTestReasonsForRejectionMarkedForRepair($motTest);
        $this->setMotTestCompletedDate($motTest);
    }

    /**
     * @param MotTest $motTest
     * @param $data
     */
    private function onAbortedByVe(MotTest $motTest, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::VE_MOT_TEST_ABORT);
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty([self::FIELD_REASON_FOR_ABORT], $data);
        $reasonForAbort = $data[self::FIELD_REASON_FOR_ABORT];
        $this->motTestStatusChangeValidator->checkMotTestCanBeAbortedByVe($motTest);

        $person = $this->motIdentityProvider->getIdentity()->getPerson();

        $motTestCancelled = new MotTestCancelled();
        $motTestCancelled->setLastUpdatedOn(new \DateTime('now'))
            ->setLastUpdatedBy($person);

        $comment = new Comment();
        $comment->setComment($reasonForAbort)
            ->setCommentAuthor($person);
        $motTestCancelled->setComment($comment);

        $motTest->setMotTestCancelled($motTestCancelled);

        // ReasonsForRejection that were flagged as "repaired" should now be permanently removed.
        $this->removeMotTestReasonsForRejectionMarkedForRepair($motTest);
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

         // ReasonsForRejection that were flagged as "repaired" should now be permanently removed.
        $this->removeMotTestReasonsForRejectionMarkedForRepair($motTest);
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

            $this->updateExpiryDateIfMysteryShopper($passedMotTest);

            if ($passedMotTest->getMotTestEmergencyReason()) {
                try{
                    $clonedMotTestEmergencyReason = clone $passedMotTest->getMotTestEmergencyReason();
                    $clonedMotTestEmergencyReason->setId(null);
                    $passedMotTest->setMotTestEmergencyReason(null);
                }catch(EntityNotFoundException $e) {
                    $clonedMotTestEmergencyReason = false;
                    $passedMotTest->setMotTestEmergencyReason(null);
                }catch(\Exception $e){
                    throw $e;
                }
            }

            $this->motTestRepository->save($passedMotTest);
            $passedMotTest->setNumber(MotTestNumberGenerator::generateMotTestNumber($passedMotTest->getId()));

            if (isset($clonedMotTestEmergencyReason) && $clonedMotTestEmergencyReason instanceof MotTestEmergencyReason) {
                $clonedMotTestEmergencyReason->setId($passedMotTest->getId());
                $passedMotTest->setMotTestEmergencyReason($clonedMotTestEmergencyReason);
            }

            $this->motTestRepository->save($passedMotTest);

            $motTest->setPrsMotTest($passedMotTest);
        }

        // -- vehicle weight --
        if ($this->shouldAmendVehicleWeight($motTest)) {
            $brakeTestVehicleWeight = $motTest->getBrakeTestResultClass3AndAbove()->getVehicleWeight();
            if (!$motTest->getMotTestType()->getIsDemo()) {
                $motTest->setVehicleWeight($brakeTestVehicleWeight);
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
     * notify only when performed by a qualifier tester (excl. VE, demo tests, and so on).
     *
     * @param MotTest $motTest
     */
    private function notifyAboutTestingOutsideHoursIfApplicable(MotTest $motTest)
    {
        if ($motTest->getMotTestType()->getIsDemo() || $motTest->getMotTestType()->isNonMotTest()) {
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
                /* @var \DvsaEntities\Entity\OrganisationBusinessRoleMap $orgPosRepository */
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

    /**
     * @param MotTest $motTest
     * @param $data
     */
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
     * Help function to create and populate a Comment.
     *
     * @param string $input
     * @param string $user
     *
     * @return Comment|null
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

    /**
     * @param MotTest $motTest
     * @param $newStatus
     *
     * @throws \Exception
     */
    private function returnSlotIfApplicable(MotTest $motTest, $newStatus)
    {
        if ($motTest->getMotTestType()->getIsDemo() || $motTest->getMotTestType()->isNonMotTest()) {
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
            throw new \Exception('MotTestType not found by code: '.$motTestTypeCode);
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
     * @param DateTimeHolder $dateTimeHolder
     *
     * @return $this
     */
    public function setDateTimeHolder($dateTimeHolder)
    {
        $this->dateTimeHolder = $dateTimeHolder;

        return $this;
    }

    /**
     * @param $motTest
     *
     * @throws UnauthorisedException
     */
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
     * @param MotTest $motTest
     * @param string  $newStatus
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
                MotTestStatusName::PASSED,
            ]
        )
        ) {
            $this->authService->assertGranted(PermissionInSystem::MOT_TEST_CONFIRM);
            $this->assertUserOwnsTheMotTest($motTest);

            if (!$motTest->getMotTestType()->isNonMotTest()) {
                $this->authService->assertGrantedAtSite(
                    PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE,
                    $motTest->getVehicleTestingStation()->getId()
                );
            }
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
     *
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

    /**
     * @param MotTest $motTest
     */
    private function removeMotTestReasonsForRejectionMarkedForRepair(MotTest $motTest)
    {
        if (!$motTest->getMotTestType() || $motTest->getMotTestType()->getCode() !== MotTestTypeCode::RE_TEST) {
            return;
        }

        foreach ($motTest->getMotTestReasonForRejections() as $motTestReasonForRejection) {
            if ($motTestReasonForRejection->isMarkedAsRepaired()) {
                $markedAsRepaired = $motTestReasonForRejection->getMarkedAsRepaired();
                $motTestReasonForRejection->undoMarkedAsRepaired();
                $this->entityManager->remove($markedAsRepaired);
                $this->entityManager->remove($motTestReasonForRejection);
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param MotTest $motTest
     *
     * @return MotTest
     */
    private function updateExpiryDateIfMysteryShopper(MotTest $motTest)
    {
        if (!$this->isMysteryShopper($motTest)) {
            return $motTest;
        }

        $mysteryShopperExpiryDate = (new MysteryShopperExpiryDateGenerator())->getCertificateExpiryDate();
        $motTest->setExpiryDate($mysteryShopperExpiryDate);

        return $motTest;
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    private function isMysteryShopper(MotTest $motTest)
    {
        return ($motTest->getMotTestType() !== null)
        && ($motTest->getMotTestType()->getCode() === MotTestTypeCode::MYSTERY_SHOPPER);
    }
}
