<?php

namespace DashboardTest\Authorisation;

use Dashboard\Authorisation\ViewNewHomepageAssertion;
use DvsaCommon\Model\ListOfRolesAndPermissions;
use DvsaCommon\Model\PersonAuthorization;
use DvsaCommon\Enum\RoleCode;

/**
 * ViewNewHomepageAssertionTest Test.
 */
class ViewNewHomepageAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testUserCanViewNewHomepageDataProvider
     *
     * @param array $systemRoles
     * @param array $organisationsRoles
     * @param array $sitesRoles
     * @param bool  $canUserViewNewHomepage
     */
    public function testUserWithSpecificRolesCanViewNewHomepage(array $systemRoles, array $organisationsRoles, array $sitesRoles, $canUserViewNewHomepage)
    {
        $personalAuthorization = $this->buildPersonAuthorization(
            $systemRoles,
            $organisationsRoles,
            $sitesRoles
        );

        $viewNewHomepageAssertion = new ViewNewHomepageAssertion($personalAuthorization);

        $canUserViewNewHomepageFromApi = $viewNewHomepageAssertion->canViewNewHomepage();

        $this->assertEquals($canUserViewNewHomepageFromApi, $canUserViewNewHomepage);
    }

    /**
     * @return array
     */
    public function testUserCanViewNewHomepageDataProvider()
    {
        return [
            [
                [RoleCode::ASSESSMENT], [], [], false,
            ],
            [
                [RoleCode::ASSESSMENT_LINE_MANAGER], [], [], false,
            ],
            [
                [RoleCode::AUTHORISED_EXAMINER], [], [], false,
            ],
            [
                [RoleCode::AUTHORISED_EXAMINER_DELEGATE], [], [], true,
            ],
            [
                [RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], [], [], true,
            ],
            [
                [RoleCode::AUTHORISED_EXAMINER_PRINCIPAL], [], [], false,
            ],
            [
                [RoleCode::AREA_OFFICE_1], [], [], false,
            ],
            [
                [RoleCode::AREA_OFFICE_2], [], [], false,
            ],
            [
                [RoleCode::CUSTOMER_SERVICE_MANAGER], [], [], false,
            ],
            [
                [RoleCode::CUSTOMER_SERVICE_OPERATIVE], [], [], false,
            ],
            [
                [RoleCode::DVLA_MANAGER], [], [], false,
            ],
            [
                [RoleCode::DVLA_OPERATIVE], [], [], false,
            ],
            [
                [RoleCode::FINANCE], [], [], false,
            ],
            [
                [RoleCode::SCHEME_MANAGER], [], [], false,
            ],
            [
                [RoleCode::SCHEME_USER], [], [], false,
            ],
            [
                [RoleCode::SITE_ADMIN], [], [], true,
            ],
            [
                [RoleCode::SITE_MANAGER], [], [], true,
            ],
            [
                [RoleCode::SLOT_PURCHASER], [], [], false,
            ],
            [
                [RoleCode::TESTER], [], [], false,
            ],
            [
                [RoleCode::TESTER_ACTIVE], [], [], true,
            ],
            [
                [RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], [], [], true,
            ],
            [
                [RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_FAILED], [], [], false,
            ],
            [
                [RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED], [], [], true,
            ],
            [
                [RoleCode::TESTER_INACTIVE], [], [], false,
            ],
            [
                [RoleCode::USER], [], [], true,
            ],
            [
                [RoleCode::VEHICLE_EXAMINER], [], [], false,
            ],
        ];
    }

    /**
     * @param array $systemRoles
     * @param array $organisationsRoles
     * @param array $sitesRoles
     *
     * @return PersonAuthorization
     *
     * @throws \Exception
     */
    protected function buildPersonAuthorization(array $systemRoles, array $organisationsRoles, array $sitesRoles)
    {
        return new PersonAuthorization(
            new ListOfRolesAndPermissions($systemRoles, []),
            $organisationsRoles,
            $sitesRoles
        );
    }
}
