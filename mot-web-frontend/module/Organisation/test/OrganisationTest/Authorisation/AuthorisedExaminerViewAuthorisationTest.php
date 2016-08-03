<?php
namespace OrganisationTest\Authorisation;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use Organisation\Authorisation\AuthorisedExaminerViewAuthorisation;
use \PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Class AuthorisedExaminerViewAuthorisationTest
 * @package OrganisationTest\Authorisation
 */
class AuthorisedExaminerViewAuthorisationTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;

    /**
     * @var AuthorisedExaminerViewAuthorisation
     */
    private $auth;
    /**
     * @var MotFrontendAuthorisationServiceInterface|MockObject
     */
    private $authMock;
    /**
     * @var MotIdentityProviderInterface|MockObject
     */
    private $identityMock;
    /**
     * @var FeatureToggles|MockObject
     */
    protected $featureToggleMock;

    public function setUp()
    {
        $this->authMock = XMock::of(MotFrontendAuthorisationServiceInterface::class);
        $this->identityMock = XMock::of(MotIdentityProviderInterface::class);
        $this->featureToggleMock = XMock::of(FeatureToggles::class);

        $this->auth = new AuthorisedExaminerViewAuthorisation($this->authMock, $this->identityMock, self::ID, $this->featureToggleMock);
    }

    public function testCanViewAuthorisedExaminerPrincipals()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::LIST_AEP_AT_AUTHORISED_EXAMINER, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewAuthorisedExaminerPrincipals());
    }

    public function testCanCreateAuthorisedExaminerPrincipal()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_CREATE, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canCreateAuthorisedExaminerPrincipal());
    }

    public function testCanRemoveAuthorisedExaminerPrincipal()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AUTHORISED_EXAMINER_PRINCIPAL_REMOVE, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canRemoveAuthorisedExaminerPrincipal());
    }

    /**
     * @dataProvider dataProviderPermissions
     * @param $permissionAtOrganisation
     * @param $featureToggle
     * @param $expected
     */
    public function testCanViewAETestQualityInformation($permissionAtOrganisation, $featureToggle, $expected)
    {
        $this->authMock->expects($this->any())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AE_VIEW_TEST_QUALITY, self::ID)
            ->willReturn($permissionAtOrganisation);

        $this->featureToggleMock->expects($this->any())
            ->method('isEnabled')
            ->with(FeatureToggle::TEST_QUALITY_INFORMATION)
            ->willReturn($featureToggle);

        $this->assertEquals($this->auth->canViewAETestQualityInformation(), $expected);
    }

    public function dataProviderPermissions()
    {
        return [
            [
                'permissionAtOrganisation' => true,
                'featureToggle'            => true,
                'expected'                 => true,
            ],
            [
                'permissionAtOrganisation' => false,
                'featureToggle'            => true,
                'expected'                 => false,
            ],
            [
                'permissionAtOrganisation' => true,
                'featureToggle'            => false,
                'expected'                 => false,
            ],
            [
                'permissionAtOrganisation' => false,
                'featureToggle'            => false,
                'expected'                 => false,
            ],
        ];
    }

    public function testCanViewVtsList()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::VEHICLE_TESTING_STATION_LIST_AT_AE, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewVtsList());
    }

    public function testCanViewVts()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtSite')
            ->with(PermissionAtSite::VEHICLE_TESTING_STATION_READ, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewVts(self::ID));
    }

    public function testCanRemovePosition()
    {
        $position = (new OrganisationPositionDto())
            ->setRole(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER)
            ->setPerson((new PersonDto())->setId(self::ID));
        $this->authMock->expects($this->once())
            ->method('assertGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::REMOVE_AEDM_FROM_AE, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canRemovePosition($position));
    }

    public function testCanViewPersonnel()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::LIST_AE_POSITIONS, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewPersonnel());
    }

    public function testCanNominate()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::NOMINATE_ROLE_AT_AE, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canNominate());
    }

    public function testCanBuySlots()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::SLOTS_PURCHASE, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canBuySlots());
    }

    public function testCanViewTransactionHistory()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::SLOTS_TRANSACTION_READ_FULL, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewTransactionHistory());
    }

    public function testCanViewSlotUsage()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AE_SLOTS_USAGE_READ, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewSlotUsage());
    }

    public function testCanViewTestLogs()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AE_TEST_LOG, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewTestLogs());
    }

    public function testCanSetDirectDebit()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::SLOTS_PAYMENT_DIRECT_DEBIT, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canSetDirectDebit());
    }

    public function testCanRefund()
    {
        $this->authMock->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::SLOTS_REFUND)
            ->willReturn(true);

        $this->assertTrue($this->auth->canRefund());
    }

    public function testCanSettlePayment()
    {
        $this->authMock->expects($this->once())
            ->method('isGranted')
            ->with(PermissionAtOrganisation::SLOTS_INSTANT_SETTLEMENT)
            ->willReturn(true);

        $this->assertTrue($this->auth->canSettlePayment());
    }

    public function testCanViewSlotBalance()
    {
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AE_SLOTS_BALANCE_READ, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewSlotBalance());
    }

    public function testCanAdjustSlotBalance()
    {
        $this->authMock->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::SLOTS_ADJUSTMENT)
            ->willReturn(true);

        $this->assertTrue($this->auth->canAdjustSlotBalance());
    }

    public function testCanViewSlotsSection()
    {
        $this->authMock->expects($this->any())
            ->method('isGrantedAtOrganisation')
            ->willReturn(false);

        $this->assertFalse($this->auth->canViewSlotsSection());
    }

    public function testCanViewAeStatus()
    {
        $this->authMock->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewAeStatus());
    }

    public function testCanSearchAe()
    {
        $this->authMock->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::AUTHORISED_EXAMINER_LIST)
            ->willReturn(true);

        $this->assertTrue($this->auth->canSearchAe());
    }

    public function testCanSearchUser()
    {
        $this->authMock->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::USER_SEARCH)
            ->willReturn(true);

        $this->assertTrue($this->auth->canSearchUser());
    }

    public function testCanViewProfile()
    {
        $position = (new OrganisationPositionDto())
            ->setStatus(BusinessRoleStatusCode::ACTIVE)
            ->setPerson((new PersonDto())->setId(self::ID));
        $this->auth->setPositions([$position]);
        $person = (new PersonDto())->setId(self::ID);
        $this->authMock->expects($this->once())
            ->method('isGrantedAtOrganisation')
            ->with(PermissionAtOrganisation::AE_EMPLOYEE_PROFILE_READ, self::ID)
            ->willReturn(true);

        $this->assertTrue($this->auth->canViewProfile($person));
    }
}
