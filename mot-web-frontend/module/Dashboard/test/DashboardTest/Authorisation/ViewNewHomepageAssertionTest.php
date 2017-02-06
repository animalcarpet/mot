<?php

namespace DashboardTest\Authorisation;

use Dashboard\Authorisation\ViewNewHomepageAssertion;
use Dashboard\Model\PersonalDetails;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Enum\RoleCode;
use DvsaCommonTest\TestUtils\XMock;

/**
 * ViewNewHomepageAssertionTest Test.
 */
class ViewNewHomepageAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider testUserCanViewNewHomepageDataProvider
     *
     * @param $systemRoles
     * @param $organisationsRoles
     * @param $sitesRoles
     * @param $canUserViewNewHomepage
     */
    public function testUserWithSpecificRolesCanViewNewHomepage($systemRoles, $organisationsRoles, $sitesRoles, $canUserViewNewHomepage)
    {
        $personalDetails = $this->buildPersonalDetails(
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
        ];
    }

    /**
     * @param array $systemRoles
     * @param array $organisationsRoles
     * @param array $sitesRoles
     *
     * @return PersonalDetails
     *
     * @throws \Exception
     */
    protected function buildPersonalDetails($systemRoles, $organisationsRoles, $sitesRoles)
    {
        return new PersonalDetails([
            'id' => 1,
            'firstName' => 'foo',
            'middleName' => 'bar',
            'surname' => 'baz',
            'username' => 'tester1',
            'dateOfBirth' => '1979-12-20',
            'title' => 'Mr',
            'gender' => 'male',
            'addressLine1' => 'foo',
            'addressLine2' => 'foo',
            'addressLine3' => 'foo',
            'town' => 'foo',
            'postcode' => 'AA11 1AA',
            'email' => 'foo',
            'emailConfirmation' => null,
            'phone' => 1234,
            'drivingLicenceNumber' => 'foo',
            'drivingLicenceRegion' => 'bar',
            'positions' => [],
            'roles' => $this->setMockRoles($systemRoles, $organisationsRoles, $sitesRoles),
        ]);
    }

    /**
     * @param array $systemRoles
     * @param array $organisationsRoles
     * @param array $sitesRoles
     *
     * @return array
     */
    private function setMockRoles($systemRoles, $organisationsRoles, $sitesRoles)
    {
        return [
            'system' => [
                'roles' => $systemRoles,
            ],
            'organisations' => [
                10 => [
                    'name' => 'testing',
                    'number' => 'VTESTING',
                    'address' => '34 Test Road',
                    'roles' => $organisationsRoles,
                ]
            ],
            'sites' => [
                20 => [
                    'name' => 'testing',
                    'number' => 'VTESTING',
                    'address' => '34 Test Road',
                    'roles' => $sitesRoles,
                ]
            ],
        ];
    }
}
