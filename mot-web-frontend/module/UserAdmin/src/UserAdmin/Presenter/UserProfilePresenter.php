<?php

namespace UserAdmin\Presenter;

use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\UrlBuilder\EventUrlBuilderWeb;
use DvsaCommon\UrlBuilder\VehicleTestingStationUrlBuilderWeb;
use Application\Helper\DataMappingHelper;
use Application\Service\CatalogService;
use Core\Presenter\AddressPresenterInterface;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Utility\AddressUtils;
use UserAdmin\Service\PersonRoleManagementService;
use UserAdmin\ViewModel\UserProfile\TesterAuthorisationViewModel;
use Zend\Di\Exception\RuntimeException;

/**
 * Decorator for PersonHelpDeskProfileDto.
 */
class UserProfilePresenter implements AddressPresenterInterface
{
    /** DVSA user profile template */
    const DVSA_PROFILE_TEMPLATE = 'user-admin/user-profile/dvsa-profile.phtml';

    /** Unrestricted user profile template path */
    const UNRESTRICTED_PROFILE_TEMPLATE = 'user-admin/user-profile/unrestricted-profile.phtml';
    /** Change email template */
    const CHANGE_EMAIL_TEMPLATE = 'user-admin/email-address/form.phtml';
    /* @var int */
    private $id;
    /* @var PersonHelpDeskProfileDto $person */
    private $person;
    /* @var bool */
    private $isDvsaUser;

    /** @var CatalogService */
    private $catalogService;

    /** @var TesterAuthorisationViewModel */
    private $testerAuthorisation;

    /** @var PersonRoleManagementService s */
    private $personRoleManagementService;

    /** @var array $dvsaRoles - cache for data retrieved from API */
    private $dvsaRoles;

    /**
     * @param PersonHelpDeskProfileDto         $person
     * @param TesterAuthorisationViewModel     $testerAuthorisation
     * @param CatalogService                   $catalogService
     * @param bool|false                       $isDvsaUser
     * @param PersonRoleManagementService|null $personRoleManagementService
     */
    public function __construct(
        PersonHelpDeskProfileDto $person,
        TesterAuthorisationViewModel $testerAuthorisation,
        CatalogService $catalogService = null,
        $isDvsaUser = false,
        PersonRoleManagementService $personRoleManagementService = null
    ) {
        $this->person = $person;
        $this->testerAuthorisation = $testerAuthorisation;
        $this->isDvsaUser = $isDvsaUser;
        $this->personRoleManagementService = $personRoleManagementService;
        $this->catalogService = $catalogService;
    }

    public function setPersonId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getPersonId()
    {
        return $this->id;
    }

    /**
     * @return string format: userName
     */
    public function displayUserName()
    {
        return $this->person->getUserName();
    }

    /**
     * @return string format: j F Y
     */
    public function displayDateOfBirth()
    {
        return DateTimeDisplayFormat::textDate($this->person->getDateOfBirth());
    }

    /**
     * @return string format: title firstName lastName
     */
    public function displayTitleAndFullName()
    {
        return implode(
            ' ', array_filter(
                [
                    $this->person->getTitle(),
                    $this->person->getFirstName(),
                    $this->person->getMiddleName(),
                    $this->person->getLastName(),
                ]
            )
        );
    }

    /**
     * @return string format: firstName lastName
     */
    public function displayFullName()
    {
        return implode(' ', [$this->person->getFirstName(), $this->person->getLastName()]);
    }

    /**
     * @return bool
     */
    public function hasDrivingLicenceNumber()
    {
        return $this->person->getDrivingLicenceNumber() != '';
    }

    /**
     * @return string
     */
    public function displayDrivingLicenceNumber()
    {
        return $this->person->getDrivingLicenceNumber();
    }

    /**
     * @return string
     */
    public function displayDrivingLicenceRegion()
    {
        $code = $this->person->getDrivingLicenceRegionCode();

        if ($code == 'NU') {
            return $this->person->getDrivingLicenceRegion();
        }

        return $code;
    }

    public function displayAuthenticationMethod()
    {
        return $this->person->getAuthenticationMethod()->getName();
    }

    /**
     * @return string
     */
    public function displayAddressLine()
    {
        return AddressUtils::stringify($this->person->getAddress());
    }

    /**
     * @return string
     */
    public function displayAddressLine1()
    {
        return $this->person->getAddress()->getAddressLine1();
    }

    /**
     * @return string
     */
    public function displayAddressLine2()
    {
        return $this->person->getAddress()->getAddressLine2();
    }

    /**
     * @return string
     */
    public function displayAddressLine3()
    {
        return $this->person->getAddress()->getAddressLine3();
    }

    /**
     * @return string
     */
    public function displayAddressLine4()
    {
        return $this->person->getAddress()->getAddressLine4();
    }

    /**
     * @return string
     */
    public function displayPostcode()
    {
        return $this->person->getAddress()->getPostcode();
    }

    /**
     * @return string
     */
    public function displayTown()
    {
        return $this->person->getAddress()->getTown();
    }

    /**
     * @return string
     */
    public function displayFullAddress()
    {
        return $this->person->getAddress()->getFullAddressString();
    }

    /**
     * @return string
     */
    public function displayEmail()
    {
        return $this->person->getEmail();
    }

    /**
     * @return string
     */
    public function displayTelephone()
    {
        return $this->person->getTelephone();
    }

    /**
     * @return string
     */
    public function displayEventsHistoryLink()
    {
        return EventUrlBuilderWeb::of()->eventList($this->getPersonId(), 'person');
    }

    /**
     * Returns an array of all site and organisation roles, grouped by site/organisation ID.
     *
     * @return array
     */
    public function getSiteAndOrganisationRoles()
    {
        $roles = $this->person->getRoles();
        $processedRoles = [];

        foreach ($roles['organisations'] as $id => $organisationData) {
            $organisationData['route'] = AuthorisedExaminerUrlBuilderWeb::of($id);
            $organisationData['roles'] = $this->getNameFromRoleCode($organisationData['roles']);
            $processedRoles[] = $organisationData;
        }

        foreach ($roles['sites'] as $id => $siteData) {
            $siteData['route'] = VehicleTestingStationUrlBuilderWeb::byId($id);
            $siteData['roles'] = $this->getNameFromRoleCode($siteData['roles']);
            $processedRoles[] = $siteData;
        }

        return $processedRoles;
    }

    /**
     * Function to make use of the service catalog and Data Mapping helper to get a role's name from codes.
     *
     * @param array $roles
     *
     * @return array
     *
     * @throws \Exception
     */
    public function getNameFromRoleCode(array $roles)
    {
        $siteAndOrganisationRoles = $this->catalogService->getBusinessRoles();
        $processedRoles = [];

        foreach ($roles as $role) {
            $temp = (new DataMappingHelper($siteAndOrganisationRoles, 'code', $role))
                ->setReturnKeys(['name'])
                ->getValue();
            $processedRoles [] = $temp['name'];
        }

        return $processedRoles;
    }

    /**
     * Get the profile template depending on the authentication status.
     *
     * @return string
     */
    public function getTemplate()
    {
        if ($this->isDvsaUser) {
            return self::DVSA_PROFILE_TEMPLATE;
        }
        // Customer service people have the power to reset passwords in this template
        return self::UNRESTRICTED_PROFILE_TEMPLATE;
    }

    /**
     * Return an array of all the internal role codes assigned to the person.
     *
     * @return array
     */
    public function getPersonAssignedInternalRoleCodes()
    {
        if (is_null($this->personRoleManagementService)) {
            throw new RuntimeException(
                sprintf(
                    'In order to use %s method you have to inject %s service during initiation of %s',
                    __METHOD__, PersonRoleManagementService::class, get_class($this)
                )
            );
        }

        if (null !== $this->dvsaRoles) {
            return $this->dvsaRoles;
        }

        $this->dvsaRoles = array_column(
            $this->personRoleManagementService->getPersonAssignedInternalRoles($this->getPersonId()),
            'name'
        );

        return $this->dvsaRoles;
    }

    public function getTesterAuthorisation()
    {
        return $this->testerAuthorisation;
    }

    /**
     * Don't display only when user has trade roles (e.g tester, AEDM, etc.) and has no DVSA/DVLA roles
     * or if user has no permission to see it.
     *
     * @return bool
     */
    public function canDisplayDvsaRoleSection()
    {
        $hasTradeRoles = count($this->getSiteAndOrganisationRoles()) > 0;

        return $this->canReadDvsaRoles() && !($hasTradeRoles && false === $this->hasDvsaRoles());
    }

    /**
     * User has permissions to:
     *  - reset password
     *  - recover username
     *  - reclaim account.
     *
     * @return bool
     */
    public function canDisplayResetSection()
    {
        return $this->personRoleManagementService->userHasPermissionToResetPassword()
            && $this->personRoleManagementService->userHasPermissionToRecoveryUsername()
            && $this->personRoleManagementService->userHasPermissionToReclaimUserAccount();
    }

    /**
     * Tells us if the user is trying to manage their own roles.
     *
     * @return bool
     */
    public function isManagingOwnRoles()
    {
        return $this->personRoleManagementService->personToManageIsSelf($this->getPersonId());
    }

    /**
     * @return bool
     */
    public function canManageDvsaRoles()
    {
        return $this->personRoleManagementService->userHasPermissionToManagePersonDvsaRoles();
    }

    /**
     * @return bool
     */
    public function canReadDvsaRoles()
    {
        return $this->personRoleManagementService->userHasPermissionToReadPersonDvsaRoles();
    }

    /**
     * @return bool
     */
    public function hasDvsaRoles()
    {
        return count($this->getPersonAssignedInternalRoleCodes()) > 0;
    }

    /**
     * @return bool
     */
    public function canDisplayDrivingLicence()
    {
        return !$this->hasDvsaRoles() &&
               !$this->personRoleManagementService->personToManageIsSelf($this->getPersonId());
    }

    /**
     * @return bool
     */
    public function canDisplayEditDrivingLicenceLink()
    {
        return $this->isDvsaUser === true;
    }

    public function hasTradeRoles()
    {
        return count($this->getSiteAndOrganisationRoles());
    }

    public function isDvsaUser()
    {
        return $this->isDvsaUser;
    }
}
