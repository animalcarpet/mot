<?php

namespace DashboardTest\Authorisation;

use Core\Service\LazyMotFrontendAuthorisationService;
use Dashboard\Authorisation\ViewNewHomepageAssertion;
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

    protected function setup()
    {
        $this->mockAuthorisationService = XMock::of(LazyMotFrontendAuthorisationService::class);
    }

    /**
     * @dataProvider testUserCanViewNewHomepageDataProvider
     *
     * @param array $roles
     * @param bool  $canUserViewNewHomepage
     */
    public function testUserWithSpecificRoleCanViewNewHomepage(array $roles, $canUserViewNewHomepage)
    {
        $this->mockAuthorisationService
            ->method('getAllRoles')
            ->willReturn($roles);

        $viewNewHomepageAssertion = new ViewNewHomepageAssertion($this->mockAuthorisationService);

        $canUserViewNewHomepageFromApi = $viewNewHomepageAssertion->canViewNewHomepage();

        $this->assertEquals($canUserViewNewHomepage, $canUserViewNewHomepageFromApi);
    }

    /**
     * @return array
     */
    public function testUserCanViewNewHomepageDataProvider()
    {
        return [
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER], false],
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER_DELEGATE], true],
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true],
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER_PRINCIPAL], false],
            [[RoleCode::USER, RoleCode::AREA_OFFICE_1], true],
            [[RoleCode::USER, RoleCode::AREA_OFFICE_2], false],
            [[RoleCode::USER, RoleCode::CENTRAL_ADMIN_TEAM], true],
            [[RoleCode::USER, RoleCode::CUSTOMER_SERVICE_MANAGER], true],
            [[RoleCode::USER, RoleCode::CUSTOMER_SERVICE_OPERATIVE], true],
            [[RoleCode::USER, RoleCode::DVLA_MANAGER], false],
            [[RoleCode::USER, RoleCode::DVLA_OPERATIVE], true],
            [[RoleCode::USER, RoleCode::FINANCE], true],
            [[RoleCode::USER, RoleCode::SCHEME_MANAGER], true],
            [[RoleCode::USER, RoleCode::SCHEME_USER], true],
            [[RoleCode::USER, RoleCode::SITE_ADMIN], true],
            [[RoleCode::USER, RoleCode::SITE_MANAGER], true],
            [[RoleCode::USER, RoleCode::TESTER], true],
            [[RoleCode::USER, RoleCode::TESTER_ACTIVE], true],
            [[RoleCode::USER, RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED], true],
            [[RoleCode::USER, RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_FAILED], false],
            [[RoleCode::USER, RoleCode::TESTER_APPLICANT_INITIAL_TRAINING_REQUIRED], true],
            [[RoleCode::USER, RoleCode::TESTER_INACTIVE], false],
            [[RoleCode::USER], true],
            [[RoleCode::USER, RoleCode::VEHICLE_EXAMINER], true],
            [[RoleCode::USER, RoleCode::TESTER, RoleCode::TESTER_ACTIVE], true],
            [[RoleCode::USER, RoleCode::AUTHORISED_EXAMINER_DELEGATE, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER], true],
        ];
    }
}
