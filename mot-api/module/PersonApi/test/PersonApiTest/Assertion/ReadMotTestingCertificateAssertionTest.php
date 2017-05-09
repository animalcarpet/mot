<?php

namespace PersonApiTest\Assertion;

use PersonApi\Assertion\ReadMotTestingCertificateAssertion;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Constants\Role;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaEntities\Entity\Person;
use PersonApi\Service\PersonalDetailsService;

class ReadMotTestingCertificateAssertionTest extends \PHPUnit_Framework_TestCase
{
    const USER_ID = 1;
    const PERSON_ID = 2;

    private $authService;
    private $identityProvider;
    private $personalDetailsService;

    public function setUp()
    {
        $this->authService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->identityProvider = XMock::of(MotIdentityProviderInterface::class);
        $this->personalDetailsService = XMock::of(PersonalDetailsService::class);
    }

    /** @return  ReadMotTestingCertificateAssertion */
    private function createAssertion()
    {
        return new ReadMotTestingCertificateAssertion($this->authService, $this->identityProvider, $this->personalDetailsService);
    }

    /**
     * @dataProvider getValidData
     */
    public function testIsGrantedReturnsTrueWhenUserCanReadMotTestingCertificates(Person $person, array $systemRoles)
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::USER_ID);

        $this
            ->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity)
        ;

        if (self::USER_ID === $person->getId()) {
            $expects = $this->exactly(0);
        } else {
            $expects = $this->once();
        }

        $this
            ->personalDetailsService
            ->expects($expects)
            ->method('assertViewGranted');

        $isGranted = $this->createAssertion()->isGranted($person, $systemRoles);
        $this->assertTrue($isGranted);
    }

    public function testIsGrantedReturnsFalseWhenUserReadOwnCertificateAndHasDvsaRole()
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::USER_ID);

        $this
            ->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity)
        ;

        $this
            ->personalDetailsService
            ->expects($this->exactly(0))
            ->method('assertViewGranted');

        $isGranted = $this->createAssertion()->isGranted((new Person())->setId(self::USER_ID), [Role::USER, Role::DVSA_AREA_OFFICE_1]);
        $this->assertFalse($isGranted);
    }

    public function testIsGrantedReturnsFalseWhenUserReadAnotherPersonCertificateAndHasNoCorrectPermission()
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::USER_ID);

        $this
            ->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity)
        ;

        $this
            ->personalDetailsService
            ->expects($this->once())
            ->method('assertViewGranted')
            ->willThrowException(new UnauthorisedException(''));

        $isGranted = $this->createAssertion()->isGranted((new Person())->setId(self::PERSON_ID), [Role::USER, Role::DVSA_AREA_OFFICE_1]);
        $this->assertFalse($isGranted);
    }

    public function testIsGrantedReturnsFalseWhenUserReadAnotherPersonCertificateAndThisPersonHasDvsaRole()
    {
        $identity = XMock::of(MotIdentityInterface::class);
        $identity
            ->expects($this->any())
            ->method('getUserId')
            ->willReturn(self::USER_ID);

        $this
            ->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity)
        ;

        $this
            ->personalDetailsService
            ->expects($this->once())
            ->method('assertViewGranted');

        $isGranted = $this->createAssertion()->isGranted((new Person())->setId(self::PERSON_ID), [Role::USER, Role::DVSA_AREA_OFFICE_1]);
        $this->assertFalse($isGranted);
    }

    public function getValidData()
    {
        return [
            [
                (new Person())->setId(self::USER_ID),
                [Role::USER],
            ],
            [
                (new Person())->setId(self::USER_ID),
                [Role::USER, Role::TESTER_ACTIVE],
            ],
            [
                (new Person())->setId(self::PERSON_ID),
                [Role::USER],
            ],
            [
                (new Person())->setId(self::PERSON_ID),
                [Role::USER, Role::TESTER_ACTIVE],
            ],
        ];
    }
}
