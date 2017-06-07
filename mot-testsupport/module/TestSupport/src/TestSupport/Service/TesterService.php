<?php

namespace TestSupport\Service;

use DvsaCommon\Enum\VehicleClassGroupCode;
use TestSupport\Helper\NotificationsHelper;
use TestSupport\Helper\TestSupportAccessTokenManager;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use Zend\View\Model\JsonModel;
use TestSupport\FieldValidation;
use TestSupport\Helper\TestSupportRestClientHelper;
use TestSupport\Model\Account;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\TestDataResponseHelper;
use Doctrine\ORM\EntityManager;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\SitePermissionsHelper;
use TestSupport\Model\AccountPerson;

class TesterService
{
    const SITE_POSITION_NOTIFICATION_ID = 5;

    /** @var TestSupportRestClientHelper */
    private $testSupportRestClientHelper;

    /** @var AccountService */
    private $accountService;

    /** @var NotificationsHelper */
    protected $notificationsHelper;

    /** @var SitePermissionsHelper */
    protected $sitePermissionsHelper;

    /** @var EntityManager */
    private $entityManager;

    /** @var TesterAuthorisationStatusService */
    private $testerAuthorisationStatusService;

    private $accountPerson;

    /**
     * @param TestSupportRestClientHelper      $testSupportRestClientHelper
     * @param NotificationsHelper              $notificationsHelper
     * @param SitePermissionsHelper            $sitePermissionsHelper
     * @param AccountService                   $accountService
     * @param EntityManager                    $entityManager
     * @param TesterAuthorisationStatusService $testerAuthorisationStatusService
     */
    public function __construct(
        TestSupportRestClientHelper $testSupportRestClientHelper,
        NotificationsHelper $notificationsHelper,
        SitePermissionsHelper $sitePermissionsHelper,
        AccountService $accountService,
        EntityManager $entityManager,
        TesterAuthorisationStatusService $testerAuthorisationStatusService
    ) {
        $this->testSupportRestClientHelper = $testSupportRestClientHelper;
        $this->accountService = $accountService;
        $this->entityManager = $entityManager;
        $this->notificationsHelper = $notificationsHelper;
        $this->sitePermissionsHelper = $sitePermissionsHelper;
        $this->testerAuthorisationStatusService = $testerAuthorisationStatusService;
    }

    /**
     * Create a tester with the data supplied.
     *
     * @param array $data
     * @param bool  $addLicence
     *
     * @return JsonModel
     */
    public function create(array $data, $addLicence = true)
    {
        FieldValidation::checkForRequiredFieldsInData(['siteIds'], $data);

        TestSupportAccessTokenManager::addSchemeManagerAsRequestorIfNecessary($data);

        if (!isset($data['personId'])) {
            $dataGeneratorHelper = DataGeneratorHelper::buildForDifferentiator($data);
            if (isset($data['contactEmail'])) {
                $data['emailAddress'] = $data['contactEmail'];
            }

            $this->accountPerson = new AccountPerson($data, $dataGeneratorHelper);
            $account = $this->accountService->createAccount(
                SiteBusinessRoleCode::TESTER,
                $dataGeneratorHelper,
                $this->accountPerson,
                $addLicence
            );
        } else {
            $account = new Account($data);
        }

        $qualifications = $data['qualifications'] ? $data['qualifications'] : [
            VehicleClassGroupCode::BIKES =>
                TesterAuthorisationStatusService::DEFAULT_QUALIFICATION_STATUS,
            VehicleClassGroupCode::CARS_ETC =>
                TesterAuthorisationStatusService::DEFAULT_QUALIFICATION_STATUS,
        ];

        $this->testerAuthorisationStatusService->setTesterQualificationStatus(
            $account->getPersonId(),
            ArrayUtils::tryGet(
                $data,
                TesterAuthorisationStatusService::CUSTOM_QUALIFICATIONS_KEY,
                $qualifications
            )
        );

        $this->addUserRolesToSite($account, $data);

        return TestDataResponseHelper::jsonOk([
            'message' => 'Tester created',
            'title' => 'Mr',
            'username' => $account->getUsername(),
            'password' => $account->getPassword(),
            'personId' => $account->getPersonId(),
            'firstName' => $account->getFirstName(),
            'middleName' => $this->accountPerson->getMiddleName(),
            'surname' => $account->getSurname(),
            'addressLine1' => $this->accountPerson->getAddressLine1(),
            'addressLine2' => $this->accountPerson->getAddressLine2(),
            'postcode' => $this->accountPerson->getPostcode(),
            'phoneNumber' => $this->accountPerson->getPhoneNumber(),
            'emailAddress' => $this->accountPerson->getEmailAddress(),
            'multiSiteUser' => (isset($data['siteIds']) && count($data['siteIds']) > 1) ? true : false,
            'dateOfBirth' => $this->accountPerson->getDateOfBirth(),
            'drivingLicenceNumber' => $this->accountPerson->getDrivingLicenceNumber(),
        ]);
    }

    /**
     * @param int    $testerId
     * @param string $group
     * @param string $statusCode
     */
    public function updateTesterQualificationStatus($testerId, $group, $statusCode)
    {
        if ($group === 'A and B') {
            $data = ['A' => $statusCode, 'B' => $statusCode];
        } else {
            $data = [$group => $statusCode];
        }

        $this->testerAuthorisationStatusService->setTesterQualificationStatus($testerId, $data);
    }

    public function insertTesterQualificationStatus($testerId, $group, $statusCode)
    {
        if (!VehicleClassGroupCode::exists($group)) {
            throw new \InvalidArgumentException("Group '".$group."' does not exist.");
        }

        $this->testerAuthorisationStatusService->insertTesterQualificationStatus($testerId, [$group => $statusCode]);
    }

    public function removeTesterQualificationStatusForGroup($testerId, $group)
    {
        $this->testerAuthorisationStatusService->deleteTesterQualificationStatusForGroup($testerId, $group);
    }

    private function addUserRolesToSite(Account $account, $data)
    {
        $this->sitePermissionsHelper->addPermissionToSites($account, SiteBusinessRoleCode::TESTER, $data['siteIds']);
    }

    /**
     * @param array $testerDetails
     *
     * @return JsonModel
     */
    public function createWithoutLicence(array $testerDetails = [])
    {
        $tester = $this->create($testerDetails, false);

        return $tester;
    }
}
