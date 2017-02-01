<?php

namespace DashboardTest\Authorisation;

use Application\Data\ApiPersonalDetails;
use Dashboard\Authorisation\ViewNewHomepageAssertion;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Enum\RoleCode;
use DvsaCommonTest\TestUtils\XMock;

/**
 * ViewNewHomepageAssertionTest Test.
 */
class ViewNewHomepageAssertionTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotAuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockAuthorisationService;

    /** @var MotFrontendIdentityInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockIdentity;

    /** @var ApiPersonalDetails|\PHPUnit_Framework_MockObject_MockObject */
    protected $mockPersonalDetailsService;

    protected function setUp()
    {
        $this->mockAuthorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->mockIdentity = XMock::of(MotFrontendIdentityInterface::class);
    }

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
        $this->mockPersonalDetailsService = $this->buildPersonalDetailsServiceMock(
            $systemRoles,
            $organisationsRoles,
            $sitesRoles
        );

        $viewNewHomepageAssertion = new ViewNewHomepageAssertion(
            $this->mockAuthorisationService,
            $this->mockIdentity,
            $this->mockPersonalDetailsService
        );
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
     * @return \PHPUnit_Framework_MockObject_MockObject
     *
     * @throws \Exception
     */
    protected function buildPersonalDetailsServiceMock($systemRoles, $organisationsRoles, $sitesRoles)
    {
        $personalDetailsServiceMock = Xmock::of(ApiPersonalDetails::class);
        $personalDetailsServiceMock
            ->method('getPersonalDetailsData')
            ->willReturn([
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

        return $personalDetailsServiceMock;
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