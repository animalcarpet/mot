<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use Api\Check\CheckMessage;
use Api\Check\CheckResult;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateUtils;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\ConfigurationRepository;
use DvsaEntities\Repository\ConfigurationRepositoryInterface;
use DvsaEntities\Repository\MotTestRepository;
use Zend\Authentication\AuthenticationService;

/**
 * Class MotTestSecurityService.
 */
class MotTestSecurityService extends AbstractService
{
    private static $CONFIG_PARAM_ODOMETER_READING_MODIFICATION_WINDOW_LENGTH_IN_DAYS
        = 'odometerReadingModificationWindowLengthInDays';

    /**
     * @var TesterService
     */
    private $testerService;

    /**
     * @var AuthenticationService
     */
    private $motIdentityProvider;

    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;

    /**
     * @var ConfigurationRepository
     */
    private $configurationRepository;

    /**
     * @var AuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotTestRepository
     */
    private $motTestRepository;

    /**
     * @var ReadMotTestAssertion
     */
    private $readMotTestAssertion;

    /**
     * @param EntityManager                    $entityManager
     * @param AuthenticationService            $motIdentityProvider
     * @param TesterService                    $testerService
     * @param ConfigurationRepositoryInterface $configurationRepository
     * @param AuthorisationServiceInterface    $authorisationService
     * @param AuthenticationService            $authenticationService
     * @param MotTestRepository                $motTestRepository
     */
    public function __construct(
        EntityManager $entityManager,
        AuthenticationService $motIdentityProvider,
        TesterService $testerService,
        ConfigurationRepositoryInterface $configurationRepository,
        AuthorisationServiceInterface $authorisationService,
        MotTestRepository $motTestRepository,
        ReadMotTestAssertion $readMotTestAssertion
    ) {
        parent::__construct($entityManager);

        $this->testerService = $testerService;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->dateTimeHolder = new DateTimeHolder();
        $this->configurationRepository = $configurationRepository;
        $this->authorisationService = $authorisationService;
        $this->motTestRepository = $motTestRepository;
        $this->readMotTestAssertion = $readMotTestAssertion;
    }

    /**
     * @param $vtsId
     *
     * @return bool
     */
    public function isCurrentTesterAssignedToVts($vtsId)
    {
        //TODO:  need to check what role/permission is appropriate to check for here.
        return $this->authorisationService->isGrantedAtSite(PermissionAtSite::MOT_TEST_ABORT_AT_SITE, $vtsId);
    }

    private function getUserId()
    {
        return $this->motIdentityProvider->getIdentity()->getUserId();
    }

    /**
     * @param $motTest
     *
     * @return bool
     */
    public function isCurrentTesterAssignedToMotTest(MotTest $motTest)
    {
        $tester = $this->testerService->getTesterByUserId($this->getUserId());

        return $tester->getId() === $motTest->getTester()->getId();
    }

    /**
     * @param $motTestNumber
     *
     * @return bool
     */
    public function canModifyOdometerForTest($motTestNumber)
    {
        $motTest = $this->getMotTest($motTestNumber);
        $this->getReadMotTestAssertion()->assertGranted($motTest);
        $allowedToUpdate = true;

        if (!$motTest->isActive()) {
            $certificateIssued = $motTest->isPassedOrFailed();
            if ($certificateIssued) {
                $allowedToUpdate = $this->isOdometerReadingModificationWindowOpen($motTest);
            } else {
                $allowedToUpdate = false;
            }
        }

        return $allowedToUpdate;
    }

    /**
     * @param \DvsaCommon\Date\DateTimeHolder $dateTimeHolder
     */
    public function setDateTimeHolder($dateTimeHolder)
    {
        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     */
    private function isOdometerReadingModificationWindowOpen(MotTest $motTest)
    {
        if (!$motTest->getIssuedDate()) {
            return false;
        }

        $checkResult = $this->validateOdometerReadingModificationWindowOpen($motTest);

        return $checkResult->isEmpty();
    }

    /**
     * @param MotTest $motTest
     *
     * @return CheckResult
     */
    public function validateOdometerReadingModificationWindowOpen(MotTest $motTest)
    {
        $checkResult = CheckResult::ok();
        $daysPassedSinceTestIssue = (int) DateUtils::getDaysDifference(
            DateUtils::cropTime($motTest->getIssuedDate()),
            $this->dateTimeHolder->getCurrentDate()
        );
        $odometerReadingModificationWindowLengthInDays = (int) $this->configurationRepository->getValue(
            self::$CONFIG_PARAM_ODOMETER_READING_MODIFICATION_WINDOW_LENGTH_IN_DAYS
        );
        if ($daysPassedSinceTestIssue > $odometerReadingModificationWindowLengthInDays) {
            $checkResult->add(
                CheckMessage::withError()->text(
                    "Odometer reading can be modified only within $odometerReadingModificationWindowLengthInDays
                     days from issuing certificate. Currently $daysPassedSinceTestIssue has passed."
                )
            );
        }

        return $checkResult;
    }

    /**
     * @param $motTestNumber
     *
     * @return MotTest
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    private function getMotTest($motTestNumber)
    {
        return $this->motTestRepository->getMotTestByNumber($motTestNumber);
    }

    /**
     * @return ReadMotTestAssertion
     */
    private function getReadMotTestAssertion()
    {
        return $this->readMotTestAssertion;
    }
}
