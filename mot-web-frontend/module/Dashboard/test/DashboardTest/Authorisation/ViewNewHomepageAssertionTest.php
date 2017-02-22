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
     * @param bool $canUserViewNewHomepage
     */
    public function testUserWithSpecificRolesCanViewNewHomepage(array $systemRoles, array $organisationsRoles, array $sitesRoles, $canUserViewNewHomepage)
    {
        $personalDetails = $this->buildPersonAuthorization(
            $systemRoles,
            $organisationsRoles,
            $sitesRoles
        );

        $viewNewHomepageAssertion = new ViewNewHomepageAssertion($personalDetails);

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
                [RoleCode::USER],
                [],
                [],
                true,
            ],
            [
                [RoleCode::USER, RoleCode::FINANCE],
                [],
                [],
                false,
            ],
            [
                [RoleCode::USER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED],
                [],
                [],
                true,
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
    protected function buildPersonAuthorization($systemRoles, $organisationsRoles, $sitesRoles)
    {
        return new PersonAuthorization(
            new ListOfRolesAndPermissions($systemRoles, []),
            $organisationsRoles,
            $sitesRoles
        );
    }
}
