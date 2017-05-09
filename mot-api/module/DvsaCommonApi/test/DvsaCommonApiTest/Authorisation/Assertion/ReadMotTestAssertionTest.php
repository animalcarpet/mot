<?php

namespace DvsaCommonApiTest\Authorisation\Assertion;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use Zend\Authentication\AuthenticationService;

class ReadMotTestAssertionTest extends \PHPUnit_Framework_TestCase
{
    private $authorisationService;
    private $identityProvider;

    public function setUp()
    {
        $this->authorisationService = XMock::of(MotAuthorisationServiceInterface::class);
        $this->identityProvider = XMock::of(AuthenticationService::class);
    }

    private function createAssertion()
    {
        return new ReadMotTestAssertion($this->authorisationService, $this->identityProvider);
    }

    public function testIsMotTestOwner_givenUserIsOwner_shouldReturnTrue()
    {
        $tester = (new Person())->setId(12);
        $motTest = (new MotTest())->setTester($tester);

        $this->setupMockIdentity($this->identityProvider);

        $this->assertTrue($this->createAssertion()->isMotTestOwner($motTest));
    }

    public function testIsMotTestOwner_givenUserIsNotOwner_shouldReturnFalse()
    {
        $tester = (new Person())->setId(230232);
        $motTest = (new MotTest())->setTester($tester);

        $this->setupMockIdentity($this->identityProvider);

        $this->assertFalse($this->createAssertion()->isMotTestOwner($motTest));
    }

    public function testIsMotTestOwnerForDto_givenUserIsOwner_shouldReturnTrue()
    {
        $tester = (new PersonDto())->setId(12);
        $motTest = (new MotTestDto())->setTester($tester);

        $this->setupMockIdentity($this->identityProvider);

        $this->assertTrue($this->createAssertion()->isMotTestOwnerForDto($motTest));
    }

    public function testIsMotTestOwnerForDto_givenUserIsNotOwner_shouldReturnFalse()
    {
        $tester = (new PersonDto())->setId(230232);
        $motTest = (new MotTestDto())->setTester($tester);

        $this->setupMockIdentity($this->identityProvider);

        $this->assertFalse($this->createAssertion()->isMotTestOwnerForDto($motTest));
    }

    public function testAssertGrantedForDemoTest_givenUserIsOwner()
    {
        $tester = (new Person())->setId(12);
        $motTest = (new MotTest())->setTester($tester);
        $type = (new MotTestType())->setIsDemo(true);
        $motTest->setMotTestType($type);

        $this->setupMockIdentity($this->identityProvider);
        $this->assertNull($this->createAssertion()->assertGranted($motTest));
    }

    public function testAssertGrantedForDemoTest_givenUserHasPermission()
    {
        $tester = (new Person())->setId(12);
        $motTest = (new MotTest())->setTester($tester);
        $type = (new MotTestType())->setIsDemo(true);
        $motTest->setMotTestType($type);

        $this->setupMockIdentity($this->identityProvider, 2100, 'ft-enf-tester');

        $this->authorisationService = new AuthorisationServiceMock();
        $this->authorisationService->granted(PermissionInSystem::MOT_DEMO_READ);

        $this->assertNull($this->createAssertion()->assertGranted($motTest));
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testAssertGrantedForDemoTest_givenUserDoesNotHavePermission()
    {
        $tester = (new Person())->setId(12);
        $motTest = (new MotTest())->setTester($tester);
        $type = (new MotTestType())->setIsDemo(true);
        $motTest->setMotTestType($type);

        $this->setupMockIdentity($this->identityProvider, 230232);

        $this->authorisationService = new AuthorisationServiceMock();

        $this->createAssertion()->assertGranted($motTest);
    }

    /**
     * Fake an identity.
     *
     * @param object $identityProvider
     * @param int    $id               The ID of the user
     * @param string $username
     */
    private function setupMockIdentity($identityProvider, $id = 12, $username = 'tester')
    {
        $identity = new MotIdentity($id, $username);

        $identityProvider->expects($this->once())
            ->method('getIdentity')
            ->willReturn($identity);
    }
}
