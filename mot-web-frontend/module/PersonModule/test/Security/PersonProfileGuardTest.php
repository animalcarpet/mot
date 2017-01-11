<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\Security;

use Dashboard\Model\PersonalDetails;
use Dashboard\Service\TradeRolesAssociationsService;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaClient\Entity\TesterAuthorisation;
use DvsaClient\Entity\TesterGroupAuthorisationStatus;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Model\OrganisationBusinessRoleCode;
use InvalidArgumentException;

class PersonProfileGuardTest extends \PHPUnit_Framework_TestCase
{
    const LOGGED_IN_PERSON_ID = 1;
    const TARGET_PERSON_ID = 2;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var TesterAuthorisation
     */
    private $testerAuthorisation;

    /**
     * @var array
     */
    private $tradeRolesAndAssociations;

    /**
     * @var PersonalDetails
     */
    private $personalDetails;

    /**
     * @var string
     */
    private $context;

    public function setUp()
    {
        $this->authorisationService = $this
            ->getMockBuilder(MotAuthorisationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->identityProvider = $this
            ->getMockBuilder(MotIdentityProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->testerAuthorisation = $this
            ->getMockBuilder(TesterAuthorisation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tradeRolesAndAssociationsService = $this
            ->getMockBuilder(TradeRolesAssociationsService::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->tradeRolesAndAssociations = [];

        $this->personalDetails = $this
            ->getMockBuilder(PersonalDetails::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->context = ContextProvider::NO_CONTEXT;
    }

    public function testIsViewingOwnProfileWhenInTheYourProfileContext()
    {
        $guard = $this
            ->withContext(ContextProvider::YOUR_PROFILE_CONTEXT)
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
        $this->assertTrue($guard->isViewingOwnProfile());
    }

    public function testIsViewingOwnProfileWhenNotInTheYourProfileContext()
    {
        $guard = $this
            ->withContext(ContextProvider::NO_CONTEXT)
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
        $this->assertFalse($guard->isViewingOwnProfile());
    }

    public function testIsViewingHimselfWhenTargetPersonIsSelf()
    {
        $guard = $this
            ->withTargetPerson(self::TARGET_PERSON_ID)
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
        $this->assertFalse($guard->isViewingHimself());
    }

    public function testIsViewingHimselfWhenTargetPersonIsOther()
    {
        $guard = $this
            ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
        $this->assertTrue($guard->isViewingHimself());
    }

    /**
     * View Date Of Birth.
     *
     * Rule: When SM, SU, AO1, AO2, VE, CSM, CSCO View AEDM, AED, SITE-M, SITE-A, TESTER, NO-ROLES.
     *
     * @return array
     */
    public function viewDateOfBirthProvider()
    {
        return [
            [
                // When INVALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, []],
                [self::TARGET_PERSON_ID, []],
                false,
            ],
            [
                // When VALID-USER View AUTHORISED_EXAMINER_DESIGNATED_MANAGER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]],
                true,
            ],
            [
                // When VALID-USER View AUTHORISED_EXAMINER_DELEGATE
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]],
                true,
            ],
            [
                // When VALID-USER View SITE_MANAGER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_MANAGER]],
                true,
            ],
            [
                // When VALID-USER View SITE_ADMIN
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_ADMIN]],
                true,
            ],
            [
                // When VALID-USER View TESTER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, [RoleCode::TESTER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, [RoleCode::USER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DATE_OF_BIRTH]],
                [self::TARGET_PERSON_ID, []],
                true,
            ],
        ];
    }

    /**
     * @dataProvider viewDateOfBirthProvider
     *
     * @param string $context
     * @param array  $loggedInPerson
     * @param array  $targetPerson
     * @param bool   $result
     */
    public function testViewDateOfBirth($context, array $loggedInPerson, array $targetPerson, $result)
    {
        $guard = $this
            ->withContext($context)
            ->withPermissions($loggedInPerson[1])
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->createPersonProfileGuard($loggedInPerson[0]);
        $this->assertEquals($result, $guard->canViewDateOfBirth());
    }

    /**
     * View Driving licence.
     *
     * Rule: When SM, SU, AO1, AO2, VE, CSM, CSCO View AEDM, AED, SITE-M, SITE-A, TESTER, NO-ROLES.
     *
     * @return array
     */
    public function viewDrivingLicenceProvider()
    {
        return [
            [
                // When INVALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, []],
                [self::TARGET_PERSON_ID, []],
                false,
            ],
            [
                // When VALID-USER View AUTHORISED_EXAMINER_DESIGNATED_MANAGER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]],
                true,
            ],
            [
                // When VALID-USER View AUTHORISED_EXAMINER_DELEGATE
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]],
                true,
            ],
            [
                // When VALID-USER View SITE_MANAGER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_MANAGER]],
                true,
            ],
            [
                // When VALID-USER View SITE_ADMIN
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_ADMIN]],
                true,
            ],
            [
                // When VALID-USER View TESTER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::TESTER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::USER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, []],
                true,
            ],
            [
                // When AEDM-USER View TESTER: false
                ContextProvider::NO_CONTEXT,
                $this->getLoggedInPersonForDataProvider('AEDM'),
                $this->getTargetPersonForDataProvider('TESTER'),
                false,
            ],
        ];
    }

    /**
     * @dataProvider viewDrivingLicenceProvider
     *
     * @param string $context
     * @param array  $loggedInPerson
     * @param array  $targetPerson
     * @param bool   $result
     */
    public function testViewDrivingLicence($context, array $loggedInPerson, array $targetPerson, $result)
    {
        $guard = $this
            ->withContext($context)
            ->withPermissions($loggedInPerson[1])
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->createPersonProfileGuard($loggedInPerson[0]);
        $this->assertEquals($result, $guard->canViewDrivingLicence());
    }

    /**
     * Change Email.
     *
     * Rule: When ANYONE View ’Your profile’ OR When SM, SU, A01, A02, VE, CSM, CSCO View ANYONE.
     *
     * @return array
     */
    public function changeEmailAddressProvider()
    {
        return [
            [
                // When NO-ROLES-USER View NO-ROLES With YOUR-PROFILE-CONTEXT
                ContextProvider::YOUR_PROFILE_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, []],
                [self::TARGET_PERSON_ID, []],
                true,
            ],
            [
                // When NO-ROLES-USER View NO-ROLES With NO-CONTEXT
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, []],
                [self::TARGET_PERSON_ID, []],
                false,
            ],
            [
                // When VALID-USER View NO-ROLES With NO-CONTEXT
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS]],
                [self::TARGET_PERSON_ID, [RoleCode::USER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES With NO-CONTEXT
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS]],
                [self::TARGET_PERSON_ID, []],
                true,
            ],
        ];
    }

    /**
     * @dataProvider changeEmailAddressProvider
     *
     * @param string $context
     * @param array  $loggedInPerson
     * @param array  $targetPerson
     * @param bool   $result
     */
    public function testChangeEmailAddress($context, array $loggedInPerson, array $targetPerson, $result)
    {
        $guard = $this
            ->withContext($context)
            ->withPermissions($loggedInPerson[1])
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->createPersonProfileGuard($loggedInPerson[0]);
        $this->assertEquals($result, $guard->canChangeEmailAddress());
    }

    /**
     * Change Driving licence.
     *
     * Rule: When SM, SU, AO1, AO2, VE View AEDM, AED, SITE-M, SITE-A, TESTER, NO-ROLES.
     *
     * @return array
     */
    public function changeDrivingLicenceProvider()
    {
        return [
            [
                // When INVALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, []],
                [self::TARGET_PERSON_ID, []],
                false,
            ],
            [
                // When VALID-USER View AEDM
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]],
                true,
            ],
            [
                // When VALID-USER View AED
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]],
                true,
            ],
            [
                // When VALID-USER View SITE-M
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_MANAGER]],
                true,
            ],
            [
                // When VALID-USER View SITE-A
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_ADMIN]],
                true,
            ],
            [
                // When VALID-USER View TESTER
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::TESTER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, [RoleCode::USER]],
                true,
            ],
            [
                // When VALID-USER View NO-ROLES
                ContextProvider::NO_CONTEXT,
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ADD_EDIT_DRIVING_LICENCE]],
                [self::TARGET_PERSON_ID, []],
                true,
            ],
        ];
    }

    /**
     * @dataProvider changeDrivingLicenceProvider
     *
     * @param string $context
     * @param array  $loggedInPerson
     * @param array  $targetPerson
     * @param bool   $result
     */
    public function testChangeDrivingLicence($context, array $loggedInPerson, array $targetPerson, $result)
    {
        $guard = $this
            ->withContext($context)
            ->withPermissions($loggedInPerson[1])
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->createPersonProfileGuard($loggedInPerson[0]);
        $this->assertEquals($result, $guard->canChangeDrivingLicence());
    }

    public function testShouldNotDisplayGroupAStatusWithEmptyStatusCode()
    {
        $guard = $this
            ->withTesterAuthorisation(null, null)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->shouldDisplayGroupAStatus());
    }

    public function testShouldNotDisplayGroupAStatusWithInitialTrainingNeededCode()
    {
        $guard = $this
            ->withTesterAuthorisation(AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, null)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->shouldDisplayGroupAStatus());
    }

    public function testShouldDisplayGroupAStatus()
    {
        $guard = $this
            ->withTesterAuthorisation(AuthorisationForTestingMotStatusCode::QUALIFIED, null)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->shouldDisplayGroupAStatus());
    }

    public function testShouldNotDisplayGroupBStatusWithEmptyStatusCode()
    {
        $guard = $this
            ->withTesterAuthorisation(null, null)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->shouldDisplayGroupBStatus());
    }

    public function testShouldNotDisplayGroupBStatusWithInitialTrainingNeededCode()
    {
        $guard = $this
            ->withTesterAuthorisation(null, AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->shouldDisplayGroupBStatus());
    }

    public function testShouldDisplayGroupBStatus()
    {
        $guard = $this
            ->withTesterAuthorisation(null, AuthorisationForTestingMotStatusCode::QUALIFIED)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->shouldDisplayGroupBStatus());
    }

    public function testCantResetAccount()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canResetAccount());
    }

    public function testCanResetAccount()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::USER_ACCOUNT_RECLAIM)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->canResetAccount());
    }

    public function testCantSendUserIdByPost()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canSendUserIdByPost());
    }

    public function testCanSendUserIdByPost()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::USERNAME_RECOVERY)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->canSendUserIdByPost());
    }

    public function testCantSendPasswordResetByPost()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canSendPasswordResetByPost());
    }

    public function testCanPasswordResetByPost()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::USER_PASSWORD_RESET)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->canSendPasswordResetByPost());
    }

    public function testCantViewTradeRoles()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canViewTradeRoles());
    }

    /**
     * View trade roles.
     *
     * @return array
     */
    public function canViewTradeRolesProvider()
    {
        return [
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER], []],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], []],
                ContextProvider::NO_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER], []],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE], []],
                ContextProvider::NO_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER], []],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_MANAGER]],
                ContextProvider::NO_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER], []],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_ADMIN]],
                ContextProvider::NO_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER], []],
                [self::TARGET_PERSON_ID, [RoleCode::TESTER]],
                ContextProvider::NO_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], []],
                [self::TARGET_PERSON_ID, [RoleCode::TESTER]],
                ContextProvider::NO_CONTEXT,
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]],
                [self::TARGET_PERSON_ID, []],
                ContextProvider::YOUR_PROFILE_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]],
                [self::TARGET_PERSON_ID, []],
                ContextProvider::YOUR_PROFILE_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], [RoleCode::SITE_ADMIN]],
                [self::TARGET_PERSON_ID, []],
                ContextProvider::YOUR_PROFILE_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], [RoleCode::SITE_MANAGER]],
                [self::TARGET_PERSON_ID, []],
                ContextProvider::YOUR_PROFILE_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], [RoleCode::TESTER]],
                [self::TARGET_PERSON_ID, []],
                ContextProvider::YOUR_PROFILE_CONTEXT,
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [], [RoleCode::USER]],
                [self::TARGET_PERSON_ID, []],
                ContextProvider::YOUR_PROFILE_CONTEXT,
                true,
            ],
        ];
    }

    /**
     * @dataProvider canViewTradeRolesProvider
     *
     * @param array  $loggedInPerson
     * @param array  $targetPerson
     * @param string $context
     * @param bool   $result
     */
    public function testCanViewTradeRoles(array $loggedInPerson, array $targetPerson, $context, $result)
    {
        $loggedInPersonPermissions = $loggedInPerson[1];
        $loggedInPersonRoles = $loggedInPerson[2];
        $context = $context ?: ContextProvider::NO_CONTEXT;

        $guard = $this
            ->withPermissions($loggedInPersonPermissions)
            ->withRoles($loggedInPersonRoles)
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->withContext($context)
            ->createPersonProfileGuard();
        $this->assertEquals($result, $guard->canViewTradeRoles());
    }

    public function testShouldNotDisplayTradeRolesWithMissingPermission()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canViewTradeRoles());
    }

    public function testShouldDisplayTradeRolesEvenWithEmptyTradeRoles()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER)
            ->withEmptyRoles()
            ->withTargetPerson(self::TARGET_PERSON_ID, [])
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
        $this->assertTrue($guard->canViewTradeRoles());
    }

    public function testShouldDisplayTradeRoles()
    {
        $this->tradeRolesAndAssociations = ['Role', 'Role2'];

        $guard = $this
            ->withPermissions(PermissionInSystem::VIEW_TRADE_ROLES_OF_ANY_USER)
            ->withTargetPerson(self::TARGET_PERSON_ID)
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
        $this->assertTrue($guard->canViewTradeRoles());
    }

    public function testCantManageDvsaRolesIfMissingPermissions()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canManageDvsaRoles());
    }

    public function testCantManageDvsaRolesIfViewingOwnProfile()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::MANAGE_DVSA_ROLES)
            ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canManageDvsaRoles());
    }

    /**
     * Manage DVSA roles.
     *
     * @return array
     */
    public function canManageDvsaRolesProvider()
    {
        return [
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::SCHEME_MANAGER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::SCHEME_USER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::AREA_OFFICE_1]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::AREA_OFFICE_2]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::VEHICLE_EXAMINER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_MANAGER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::DVLA_MANAGER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::DVLA_OPERATIVE]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::MANAGE_DVSA_ROLES]],
                [self::TARGET_PERSON_ID, [RoleCode::FINANCE]],
                true,
            ],
        ];
    }

    /**
     * @dataProvider canManageDvsaRolesProvider
     */
    public function testCanManageDvsaRoles(array $loggedInPerson, array $targetPerson, $result)
    {
        $guard = $this
            ->withPermissions($loggedInPerson[1])
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->createPersonProfileGuard();
        $this->assertEquals($result, $guard->canManageDvsaRoles());
    }

    /**
     * Change tester qualification status.
     *
     * @return array
     */
    public function canChangeTesterQualificationStatusProvider()
    {
        return [
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_MANAGER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::SITE_ADMIN]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::TESTER]],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, []],
                [self::TARGET_PERSON_ID, []],
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, []],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [], AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, null],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [], null, AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED],
                true,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE], AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, null],
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE], null, AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED],
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE], AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED],
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE], AuthorisationForTestingMotStatusCode::QUALIFIED, null],
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE], null, AuthorisationForTestingMotStatusCode::QUALIFIED],
                false,
            ],
            [
                [self::LOGGED_IN_PERSON_ID, [PermissionInSystem::ALTER_TESTER_AUTHORISATION_STATUS]],
                [self::TARGET_PERSON_ID, [RoleCode::CUSTOMER_SERVICE_OPERATIVE], AuthorisationForTestingMotStatusCode::QUALIFIED, AuthorisationForTestingMotStatusCode::QUALIFIED],
                true,
            ],
        ];
    }

    /**
     * @dataProvider canChangeTesterQualificationStatusProvider
     */
    public function testCanChangeTesterQualificationStatus(array $loggedInPerson, array $targetPerson, $result)
    {
        $groupAStatus = isset($targetPerson[2]) ? $targetPerson[2] : null;
        $groupBStatus = isset($targetPerson[3]) ? $targetPerson[3] : null;

        $guard = $this
            ->withPermissions($loggedInPerson[1])
            ->withTargetPerson($targetPerson[0], $targetPerson[1])
            ->withTesterAuthorisation($groupAStatus, $groupBStatus)
            ->createPersonProfileGuard();
        $this->assertEquals($result, $guard->canChangeTesterQualificationStatus());
    }

    public function testCantViewEventHistory()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canViewEventHistory());
    }

    public function testCanViewEventHistory()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::LIST_EVENT_HISTORY)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->canViewEventHistory());
    }

    public function testCantViewAccountSecurity()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->withTargetPerson(self::TARGET_PERSON_ID)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canViewAccountSecurity());

        $invalidContexts = [ContextProvider::AE_CONTEXT,
            ContextProvider::NO_CONTEXT,
            ContextProvider::USER_SEARCH_CONTEXT,
            ContextProvider::VTS_CONTEXT,];

        foreach ($invalidContexts as $context) {
            $guard = $this
                ->withEmptyPermissions()
                ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
                ->withContext($context)
            ->createPersonProfileGuard();
            $this->assertFalse($guard->canViewAccountSecurity());
        }
    }

    public function testCanViewAccountSecurity()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
            ->withContext(ContextProvider::YOUR_PROFILE_CONTEXT)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->canViewAccountSecurity());
    }

    public function testCantViewManageAccountManagement()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canViewAccountManagement());

        $guard = $this
            ->withPermissions(PermissionInSystem::MANAGE_USER_ACCOUNTS)
            ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
            ->withContext(ContextProvider::YOUR_PROFILE_CONTEXT)
            ->createPersonProfileGuard();
        $this->assertFalse($guard->canViewAccountManagement());
    }

    public function testCanViewAccountManagement()
    {
        $validContexts = [ContextProvider::AE_CONTEXT,
            ContextProvider::NO_CONTEXT,
            ContextProvider::USER_SEARCH_CONTEXT,
            ContextProvider::VTS_CONTEXT,];

        foreach ($validContexts as $context) {
            $guard = $this
                ->withPermissions(PermissionInSystem::MANAGE_USER_ACCOUNTS)
                ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
                ->withContext($context)
                ->createPersonProfileGuard();
            $this->assertTrue($guard->canViewAccountManagement());
        }

        $guard = $this
            ->withPermissions(PermissionInSystem::MANAGE_USER_ACCOUNTS)
            ->withTargetPerson(self::TARGET_PERSON_ID)
            ->createPersonProfileGuard();
        $this->assertTrue($guard->canViewAccountManagement());
    }

    /**
     * @dataProvider contextProvider
     *
     * @param string $contextName
     * @param bool   $shouldThrowException
     */
    public function testInvalidContextsWillThrowException($contextName, $shouldThrowException)
    {
        if (true === $shouldThrowException) {
            $this->setExpectedException(InvalidArgumentException::class);
        }

        $guard = $this
            ->withEmptyPermissions()
            ->withContext($contextName)
            ->createPersonProfileGuard(self::LOGGED_IN_PERSON_ID);
    }

    /**
     * @return array
     */
    public function contextProvider()
    {
        return [
            [ContextProvider::NO_CONTEXT, false],
            [ContextProvider::AE_CONTEXT, false],
            [ContextProvider::VTS_CONTEXT, false],
            [ContextProvider::USER_SEARCH_CONTEXT, false],
            [ContextProvider::YOUR_PROFILE_CONTEXT, false],
            ['Invalid context', true],
            ['', true],
            [null, true],
        ];
    }

    public function testCanEditName()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::EDIT_PERSON_NAME)
            ->withTargetPerson(self::TARGET_PERSON_ID)
            ->createPersonProfileGuard();

        $this->assertTrue($guard->canEditName());
    }

    public function testViewingSelfCantEditName()
    {
        $guard = $this
            ->withPermissions(PermissionInSystem::EDIT_PERSON_NAME)
            ->withTargetPerson(self::LOGGED_IN_PERSON_ID)
            ->createPersonProfileGuard();

        $this->assertFalse($guard->canEditName());
    }

    public function testNoPermissionCantEditName()
    {
        $guard = $this
            ->withEmptyPermissions()
            ->withTargetPerson(self::TARGET_PERSON_ID)
            ->createPersonProfileGuard();

        $this->assertFalse($guard->canEditName());
    }

    public function shouldShowTesterQualificiatonStatusBoxProvider()
    {
        return [
            [AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, false],
            [AuthorisationForTestingMotStatusCode::QUALIFIED, AuthorisationForTestingMotStatusCode::INITIAL_TRAINING_NEEDED, true],
        ];
    }

    /**
     * @dataProvider shouldShowTesterQualificiatonStatusBoxProvider
     * @param AuthorisationForTestingMotStatusCode $groupAStatus
     * @param AuthorisationForTestingMotStatusCode $groupBStatus
     * @param bool $shouldDisplay
     */
    public function testShouldShowTesterQualificationStatusBox($groupAStatus, $groupBStatus, $shouldDisplay)
    {
        $guard = $this
            ->withTesterAuthorisation($groupAStatus, $groupBStatus)
            ->createPersonProfileGuard();

        $this->assertEquals($guard->shouldDisplayTesterQualificationStatusBox(), $shouldDisplay);
    }

    /**
     * @return $this
     */
    private function withEmptyPermissions()
    {
        $this
            ->authorisationService
            ->method('isGranted')
            ->willReturn(false);

        return $this;
    }

    /**
     * @param array|string $permissionsToEnable
     *
     * @return $this
     */
    private function withPermissions($permissionsToEnable)
    {
        $permissionsToEnable = (array) $permissionsToEnable;

        if (empty($permissionsToEnable)) {
            return $this;
        }

        $this
            ->authorisationService
            ->method('isGranted')
            ->will($this->returnCallback(function () use ($permissionsToEnable) {
                $args = func_get_args();

                return in_array($args[0], $permissionsToEnable);
            }));

        return $this;
    }

    /**
     * @return $this
     */
    private function withEmptyRoles()
    {
        $this
            ->authorisationService
            ->method('hasRole')
            ->willReturn(false);

        $this
            ->authorisationService
            ->method('getRolesAsArray')
            ->willReturn([]);

        return $this;
    }

    /**
     * @param array|string $activeRoles
     *
     * @return $this
     */
    private function withRoles(array $activeRoles)
    {
        $this
            ->authorisationService
            ->method('hasRole')
            ->will($this->returnCallback(function () use ($activeRoles) {
                $args = func_get_args();

                return in_array($args[0], $activeRoles);
            }));

        $this
            ->authorisationService
            ->method('getRolesAsArray')
            ->willReturn($activeRoles);

        return $this;
    }

    /**
     * @param string|null $groupACode
     * @param string|null $groupBCode
     *
     * @return $this
     */
    private function withTesterAuthorisation($groupACode = null, $groupBCode = null)
    {
        $groupAStatus = $this
            ->getMockBuilder(TesterGroupAuthorisationStatus::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupAStatus
            ->method('getCode')
            ->willReturn($groupACode);

        $groupBStatus = $this
            ->getMockBuilder(TesterGroupAuthorisationStatus::class)
            ->disableOriginalConstructor()
            ->getMock();
        $groupBStatus
            ->method('getCode')
            ->willReturn($groupBCode);

        $this
            ->testerAuthorisation
            ->method('getGroupAStatus')
            ->willReturn($groupAStatus);

        $this
            ->testerAuthorisation
            ->method('getGroupBStatus')
            ->willReturn($groupBStatus);

        return $this;
    }

    /**
     * @param string $context
     *
     * @return $this
     */
    private function withContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param int   $targetPersonId
     * @param array $roles
     *
     * @return $this
     */
    private function withTargetPerson($targetPersonId, $roles = [])
    {
        $this
            ->personalDetails
            ->method('getId')
            ->willReturn($targetPersonId);

        $this
            ->personalDetails
            ->method('getRoles')
            ->willReturn($roles);

        return $this;
    }

    /**
     * @param int $loggedInPersonId
     *
     * @return PersonProfileGuard
     */
    private function createPersonProfileGuard($loggedInPersonId = self::LOGGED_IN_PERSON_ID)
    {
        /** @var MotIdentityInterface $motIdentity */
        $motIdentity = $this->getMock(MotIdentityInterface::class);
        $motIdentity
            ->method('getUserId')
            ->willReturn($loggedInPersonId);

        $this
            ->identityProvider
            ->method('getIdentity')
            ->willReturn($motIdentity);

        return new PersonProfileGuard($this->authorisationService, $this->identityProvider, $this->personalDetails,
            $this->testerAuthorisation, $this->tradeRolesAndAssociations, $this->context);
    }

    /**
     * Warning: this method makes assumptions about the database data which might fall out of sync.
     *
     * @param $role
     *
     * @return array
     */
    private function getLoggedInPersonForDataProvider($role)
    {
        $role = strtoupper($role);
        $availableRoles = ['AED', 'AEDM','AO1', 'AO2', 'CSCO', 'CSM', 'NO-ROLES', 'SITE-A', 'SITE-M', 'SM', 'SU',
            'TESTER', 'VE', ];
        if (!in_array($role, $availableRoles)) {
            throw new InvalidArgumentException(sprintf('Unknown role "%s"', $role));
        }

        $permissions = [];

        switch ($role) {
            case 'AEDM':
                break;
        }

        return [
            self::LOGGED_IN_PERSON_ID,
            $permissions,
        ];
    }

    /**
     * Warning: this method makes assumptions about the database data which might fall out of sync.
     *
     * @param $role
     *
     * @return array
     */
    private function getTargetPersonForDataProvider($role)
    {
        $role = strtoupper($role);
        $availableRoles = ['AED', 'AEDM','AO1', 'AO2', 'CSCO', 'CSM', 'NO-ROLES', 'SITE-A', 'SITE-M', 'SM', 'SU',
            'TESTER', 'VE', ];
        if (!in_array($role, $availableRoles)) {
            throw new InvalidArgumentException(sprintf('Unknown role "%s"', $role));
        }

        $roles = [];

        switch ($role) {
            case 'TESTER':
                $roles[] = [RoleCode::TESTER];
                break;
        }

        return [
            self::TARGET_PERSON_ID,
            $roles,
        ];
    }
}
