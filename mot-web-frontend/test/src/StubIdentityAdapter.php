<?php

namespace Dvsa\Mot\Frontend\Test;

use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaCommon\Model\PersonAuthorization;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class StubIdentityAdapter implements AdapterInterface
{
    private $username;
    private $userId;
    private $vts;
    private $loggedIn;

    /**
     * @var PersonAuthorization
     */
    private $personAuthorization;

    /**
     * Ctor: Allows alternative credentials to be supplied.
     *
     * @param string              $username            String contains the login name
     * @param int                 $userid              contains the internal database user id
     * @param PersonAuthorization $personAuthorization authorization of person
     * @param bool                $atVts               whether to be logged in at a VTS
     * @param bool                $loggedIn            Whether the authentication call should succeed or not
     */
    private function __construct($username, $userid, PersonAuthorization $personAuthorization, $atVts, $loggedIn)
    {
        $this->username = $username;
        $this->userId = $userid;
        $this->loggedIn = $loggedIn;
        $this->personAuthorization = $personAuthorization;
        if ($atVts) {
            $this->vts = self::createStubVts();
        } else {
            $this->vts = null;
        }
    }

    /**
     * @return PersonAuthorization
     */
    private static function authorizationForVe()
    {
        return new PersonAuthorization(
            new ListOfRolesAndPermissions([Role::VEHICLE_EXAMINER], [PermissionInSystem::MOT_TEST_START])
        );
    }

    /**
     * Zend authentication interface (AdapterInterface) callback.
     *
     * @return Result
     */
    public function authenticate()
    {
        if ($this->loggedIn) {
            $identity = new Identity();
            $identity
                ->setUserId($this->userId)
                ->setUsername($this->username)
                ->setDisplayName('PHP Lover')
                ->setDisplayRole('Unit test monkey')
                ->setCurrentVts($this->vts)
                ->setPersonAuthorization($this->personAuthorization)
                ->setAccessToken(1111.2222);

            return new Result(Result::SUCCESS, $identity);
        } else {
            return new Result(Result::FAILURE_UNCATEGORIZED, null);
        }
    }

    /**
     * @return VehicleTestingStation
     */
    public static function createStubVts()
    {
        $vts = (new VehicleTestingStation())
            ->setVtsId(1)
            ->setSiteNumber('V1234')
            ->setName('Some Garage')
            ->setAddress('1 Some Street, Some Town, Some City, SC12 3AB')
            ->setSlots(100);

        return $vts;
    }

    /**
     * Helper: Answers an instance usable as a VehicleExaminer based on test data values.
     *
     * @return StubIdentityAdapter
     */
    public static function asVehicleExaminer()
    {
        return new StubIdentityAdapter(
            'ft-Enf-tester',
            2100,
            self::authorizationForVe(),
            false,
            true
        );
    }

    /**
     * @return StubIdentityAdapter
     */
    public static function asVehicleExaminerAtSite()
    {
        return new StubIdentityAdapter(
            'ft-Enf-tester',
            2100,
            self::authorizationForVe(),
            true,
            true
        );
    }

    /**
     * @return StubIdentityAdapter
     */
    public static function asTesterWithoutVtsChosen()
    {
        return new StubIdentityAdapter('tester1', 1, self::authorizationForTester(), false, true);
    }

    /**
     * @return StubIdentityAdapter
     */
    public static function asEnforcement()
    {
        return new StubIdentityAdapter('ft-enf-tester', 2100, PersonAuthorization::emptyAuthorization(), true, true);
    }

    /**
     * @param int    $userId
     * @param string $userName
     *
     * @return StubIdentityAdapter
     */
    public static function asTester($userId = 1, $userName = 'tester1')
    {
        return new StubIdentityAdapter($userName, $userId, self::authorizationForTester(), true, true);
    }

    /**
     * @return StubIdentityAdapter
     */
    public static function asApplicant()
    {
        return new StubIdentityAdapter('applicant-user', 9, PersonAuthorization::emptyAuthorization(), true, true);
    }

    /**
     * @return StubIdentityAdapter
     */
    public static function asAedm()
    {
        return new StubIdentityAdapter('aedm-user', 5, PersonAuthorization::emptyAuthorization(), true, true);
    }

    /**
     * @return StubIdentityAdapter
     */
    public static function asAnonymous()
    {
        return new StubIdentityAdapter('none', -1, PersonAuthorization::emptyAuthorization(), false, false);
    }

    /**
     * @param int    $userId
     * @param string $userName
     *
     * @return StubIdentityAdapter
     */
    public static function asSchemauser($userId = 28, $userName = 'schemeuser')
    {
        return new StubIdentityAdapter($userName, $userId, self::authorizationForSchemauser(), true, true);
    }

    /**
     * @return PersonAuthorization
     */
    private static function authorizationForTester()
    {
        return new PersonAuthorization(
            new ListOfRolesAndPermissions(
                [
                    SiteBusinessRoleCode::TESTER,
                    Role::USER,
                ],
                [
                    PermissionInSystem::MOT_TEST_PERFORM,
                    PermissionAtSite::MOT_TEST_PERFORM_AT_SITE,
                    PermissionInSystem::AUTHORISED_EXAMINER_LIST,
                    PermissionInSystem::TESTER_READ,
                    PermissionInSystem::DATA_CATALOG_READ,
                    PermissionInSystem::MOT_TEST_CONFIRM,
                    PermissionInSystem::MOT_TEST_START,
                    PermissionInSystem::VE_MOT_TEST_ABORT,
                    PermissionAtSite::MOT_TEST_ABORT_AT_SITE,
                ]
            ),
            [],
            [
                1 => new ListOfRolesAndPermissions(
                    [
                        SiteBusinessRoleCode::TESTER,
                    ],
                    [
                        PermissionAtSite::VEHICLE_TESTING_STATION_READ,
                        PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE,
                        PermissionAtSite::MOT_TEST_PERFORM_AT_SITE,
                        PermissionInSystem::CERTIFICATE_READ,
                        PermissionInSystem::MOT_TEST_CONFIRM,
                        PermissionAtSite::MOT_TEST_CONFIRM_AT_SITE,
                        PermissionInSystem::TESTER_RFR_ITEMS_NOT_TESTED,
                        PermissionInSystem::RFR_LIST,
                        PermissionInSystem::LATEST_VEHICLE_MOT_TEST_HISTORY_VIEW,
                        PermissionInSystem::VEHICLE_READ,
                        PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT,
                        PermissionInSystem::MOT_TEST_LIST,
                        PermissionAtSite::VIEW_TESTS_IN_PROGRESS_AT_VTS,
                        PermissionInSystem::TESTER_READ,
                        PermissionInSystem::MOT_TEST_READ,
                        PermissionInSystem::CERTIFICATE_REPLACEMENT,
                        PermissionInSystem::VEHICLE_CREATE,
                        PermissionInSystem::SLOTS_VIEW,
                        PermissionAtSite::MOT_TEST_ABORT_AT_SITE,
                    ]
                ),
            ],
            []
        );
    }

    /**
     * @return PersonAuthorization
     */
    private static function authorizationForSchemauser()
    {
        return new PersonAuthorization(
            new ListOfRolesAndPermissions(
                [
                    Role::DVSA_SCHEME_USER,
                    Role::USER,
                ],
                [
                    PermissionInSystem::VIEW_OTHER_USER_PROFILE,
                    PermissionInSystem::NOTIFICATION_READ,
                    PermissionInSystem::NOTIFICATION_ACTION,
                    PermissionInSystem::NOTIFICATION_UPDATE,
                    PermissionInSystem::NOTIFICATION_DELETE,
                    PermissionInSystem::SPECIAL_NOTICE_CREATE,
                    PermissionInSystem::SPECIAL_NOTICE_UPDATE,
                    PermissionInSystem::SPECIAL_NOTICE_REMOVE,
                    PermissionInSystem::SPECIAL_NOTICE_READ,
                    PermissionInSystem::SPECIAL_NOTICE_READ_REMOVED,
                ]
            ),
            []
        );
    }
}
