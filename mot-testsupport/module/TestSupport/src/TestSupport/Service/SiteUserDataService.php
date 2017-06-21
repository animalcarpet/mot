<?php

namespace TestSupport\Service;

use TestSupport\FieldValidation;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\NotificationsHelper;
use TestSupport\Helper\RestClientGetterTrait;
use TestSupport\Helper\SitePermissionsHelper;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Model\Account;
use TestSupport\Model\AccountPerson;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\View\Model\JsonModel;

/**
 * Creates Site Managers OR Site users for use by tests.
 *
 * Should not be deployed in production.
 */
class SiteUserDataService
{
    const STATUS_ID_ACCEPTED = 2;
    const SITE_POSITION_NOTIFICATION_ID = 5;

    /**
     * @var NotificationsHelper
     */
    private $notificationsHelper;

    /**
     * @var SitePermissionsHelper
     */
    private $sitePermissionsHelper;

    /**
     * @var AccountService
     */
    private $accountService;

    /**
     * @var AccountPerson
     */
    private $accountPerson;

    public function __construct(
        NotificationsHelper $notificationsHelper,
        SitePermissionsHelper $sitePermissionsHelper,
        AccountService $accountService
    ) {
        $this->notificationsHelper = $notificationsHelper;
        $this->sitePermissionsHelper = $sitePermissionsHelper;
        $this->accountService = $accountService;
    }

    /**
     * @param array  $data optional data with differentiator,
     *                     requestor => username and password of AEDM/AED with whom to assign site manager role
     *                     siteIds -> list of VTSs for site manager
     * @param string $role
     *
     * @return JsonModel username of new Site User (Manager Or Admin)
     */
    public function create($data, $role)
    {
        FieldValidation::checkForRequiredFieldsInData(['siteIds'], $data);

        if (!isset($data['personId'])) {
            $dataGeneratorHelper = DataGeneratorHelper::buildForDifferentiator($data);
            $this->accountPerson = new AccountPerson($data, $dataGeneratorHelper);

            $account = $this->accountService->createAccount(
                $role,
                $dataGeneratorHelper, $this->accountPerson
            );
        } else {
            $account = new Account($data);
        }

        $this->giveUserRoleAtSites($account, $role, $data);

        return TestDataResponseHelper::jsonOk(
            [
                'message' => $role.' created',
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
            ]
        );
    }

    /**
     * @param Account $account
     * @param string  $role
     * @param array   $data
     */
    private function giveUserRoleAtSites(Account $account, $role, array $data)
    {
        $this->sitePermissionsHelper->addPermissionToSites($account, $role, $data['siteIds']);
    }
}
