<?php

namespace PersonApiTest\Assertion;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApiTest\Stub\ApiIdentityProviderStub;
use DvsaCommonApiTest\Stub\IdentityStub;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use PersonApi\Assertion\MotTestingAnnualCertificateAssertion;
use PersonApi\Dto\PersonDetails;
use PersonApi\Service\PersonalDetailsService;

class MotTestingAnnualCertificateAssertionTest extends \PHPUnit_Framework_TestCase
{
    /** @var AuthorisationServiceMock */
    private $authorisationService;
    /** @var ApiIdentityProviderStub */
    private $identityProvider;
    /** @var PersonalDetailsService|\PHPUnit_Framework_MockObject_MockObject */
    private $personalDetailsService;

    public function setUp()
    {
        $this->authorisationService = new AuthorisationServiceMock();
        $this->identityProvider = new ApiIdentityProviderStub();
        $this->personalDetailsService = XMock::of(PersonalDetailsService::class);
    }

    private function createSut()
    {
        return new MotTestingAnnualCertificateAssertion(
            $this->authorisationService,
            $this->identityProvider,
            $this->personalDetailsService
        );
    }

    /**
     * @dataProvider dataProviderTestAssertGranted
     *
     * @param int   $loggedUserId
     * @param int   $personId
     * @param bool  $viewGranted
     * @param bool  $permissionGranted
     * @param array $systemRoles
     * @param bool  $exceptionExpected
     */
    public function testAssertGrantedView(
        $loggedUserId,
        $personId,
        $viewGranted,
        $permissionGranted,
        $systemRoles,
        $exceptionExpected = false
    ) {
        if ($permissionGranted == true) {
            $permission = PermissionInSystem::VIEW_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER;
        } else {
            $permission = null;
        }
        $this->setUpBeforeAssertGrantedTest($loggedUserId, $viewGranted, $permission, $systemRoles);

        if ($exceptionExpected) {
            $this->setExpectedException(UnauthorisedException::class);
        }

        $this->createSut()->assertGrantedView((new Person())->setId($personId));
    }

    public function testAssertGrantedView_throwException_whenUserIsNotAssignedToSite()
    {
        $this->setExpectedException(UnauthorisedException::class);

        $this->setUpBeforeAssertGrantedTest(1, false, null, []);
        $this->createSut()->assertGrantedView((new Person())->setId(105), 111);
    }

    public function dataProviderTestAssertGrantedView()
    {
        return [
            //User viewing his own profile with no permission - [OK]
            [1, 1, true, false, [], null],
            //User viewing other profile with no permission - [EXCEPTION]
            [1, 2, true, false, [], true],
            //User viewing other profile with permission - [OK]
            [1, 2, true, true, [], null],
            //User viewing other profile with permission but profile has DVSA role - [EXCEPTION]
            [1, 2, true, true, [RoleCode::AREA_OFFICE_1], true],
            //User has permission to view certificate, but not to view person - [EXCEPTION]
            [1, 2, false, true, [], true],
        ];
    }

    /**
     * @dataProvider dataProviderTestAssertGranted
     *
     * @param int   $loggedUserId
     * @param int   $personId
     * @param bool  $viewGranted
     * @param bool  $permissionGranted
     * @param array $systemRoles
     * @param bool  $exceptionExpected
     */
    public function testAssertGrantedCreate(
        $loggedUserId,
        $personId,
        $viewGranted,
        $permissionGranted,
        $systemRoles,
        $exceptionExpected = false
    ) {
        if ($permissionGranted == true) {
            $permission = PermissionInSystem::CREATE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER;
        } else {
            $permission = null;
        }

        $this->setUpBeforeAssertGrantedTest($loggedUserId, $viewGranted, $permission, $systemRoles);

        if ($exceptionExpected) {
            $this->setExpectedException(UnauthorisedException::class);
        }

        $this->createSut()->assertGrantedCreate((new Person())->setId($personId));
    }

    public function dataProviderTestAssertGranted()
    {
        return [
            //User viewing his own profile with no permission - [OK]
            [1, 1, true, false, [], false],
            //User viewing other profile with no permission - [EXCEPTION]
            [1, 2, true, false, [], true],
            //User viewing other profile with permission - [OK]
            [1, 2, true, true, [], false],
            //User viewing other profile with permission but profile has DVSA role - [EXCEPTION]
            [1, 2, true, true, [RoleCode::AREA_OFFICE_1], true],
            //User has permission to view certificate, but not to view person - [EXCEPTION]
            [1, 2, false, true, [], true],
        ];
    }

    /**
     * @dataProvider dataProviderTestAssertGranted
     *
     * @param int   $loggedUserId
     * @param int   $personId
     * @param bool  $viewGranted
     * @param bool  $permissionGranted
     * @param array $systemRoles
     * @param bool  $exceptionExpected
     */
    public function testAssertGrantedUpdate(
        $loggedUserId,
        $personId,
        $viewGranted,
        $permissionGranted,
        $systemRoles,
        $exceptionExpected = false
    ) {
        if ($permissionGranted == true) {
            $permission = PermissionInSystem::UPDATE_MOT_TESTING_ANNUAL_CERTIFICATE_FOR_USER;
        } else {
            $permission = null;
        }

        $this->setUpBeforeAssertGrantedTest($loggedUserId, $viewGranted, $permission, $systemRoles);

        if ($exceptionExpected) {
            $this->setExpectedException(UnauthorisedException::class);
        }

        $this->createSut()->assertGrantedUpdate((new Person())->setId($personId));
    }

    private function createPersonDetails($systemRoles)
    {
        $dto = XMock::of(PersonDetails::class);
        $dto->expects($this->any())
            ->method('getRoles')
            ->willReturn([
                'system' => [
                    'roles' => $systemRoles,
                ],
            ]);

        return $dto;
    }

    private function setUpBeforeAssertGrantedTest($loggedUserId, $viewGranted, $permission, $systemRoles)
    {
        $identity = new IdentityStub();
        $identity->setUserId($loggedUserId);
        $this->identityProvider->setIdentity($identity);

        if ($permission != null) {
            $this->authorisationService->clearAll();
            $this->authorisationService->granted($permission);
        }

        $assertViewGrantedMethod = $this->personalDetailsService
            ->expects($this->any())
            ->method('assertViewGranted');

        if ($viewGranted == true) {
            $assertViewGrantedMethod->willReturn($viewGranted);
        } elseif ($viewGranted == false) {
            $assertViewGrantedMethod->willThrowException(new UnauthorisedException('mocked unauthorised exception'));
        }

        $this->personalDetailsService->expects($this->any())
            ->method('get')
            ->willReturn($this->createPersonDetails($systemRoles));
    }
}
