<?php

namespace DvsaMotApiTest\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use DvsaCommonTest\TestUtils\TestCasePermissionTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\ModelDetail;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Vehicle;
use DvsaMotApi\Service\MotTestOptionsService;
use DvsaEntities\Repository\MotTestRepository;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use Zend\Authentication\AuthenticationService;
use DvsaEntities\Entity\Person;
use DvsaCommon\Auth\MotIdentity;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommon\Date\DateTimeApiFormat;

/**
 * Unit test for MotTestOptionsService.
 */
class MotTestOptionsServiceTest extends AbstractMotTestServiceTest
{
    use TestCasePermissionTrait;

    /** @var MotTestRepository */
    private $motTestRepository;

    /** @var AuthorisationServiceInterface */
    private $authorisationService;

    /** @var ReadMotTestAssertion */
    private $readMotTestAssertion;

    /** @var AuthenticationService */
    private $identityProvider;

    /** @var Person */
    private $tester;

    /** @var \DateTime */
    private $motTestStartedDate;

    public function setUp()
    {
        $this->motTestRepository = XMock::of(MotTestRepository::class);
        $this->readMotTestAssertion = XMock::of(ReadMotTestAssertion::class);
        $this->identityProvider = XMock::of(AuthenticationService::class);
        $this->authorisationService = AuthorisationServiceMock::class;
        $this->tester = (new Person())->setId(20);

        $this->motTestStartedDate = new \DateTime('now');
    }

    /**
     * @expectedException \DvsaCommon\Exception\UnauthorisedException
     */
    public function testUserDoesNotHavePermissionToReadTestThrowsException()
    {
        $motTest = $this->getMockMotTestEntity();
        $motTest->setMotTestType((new MotTestType())->setIsDemo(true));

        $this->setupMockIdentity($this->identityProvider, 230232);

        $this->authorisationService = new AuthorisationServiceMock();
        $this->readMotTestAssertion = $this->createAssertion();

        $this->setMotTestRepositoryResult('getMotTestByNumber', $motTest);

        $this->getService()->getOptions(1);
    }

    public function testGetMotTestWillReturnMotTestOptionsDtoWithMatchingResults()
    {
        $motTest = $this->getMockMotTestEntity();

        $this->setupMockIdentity($this->identityProvider, 20);

        $this->authorisationService = new GrantAllAuthorisationServiceStub();
        $this->readMotTestAssertion = $this->createAssertion();

        $this->setMotTestRepositoryResult('getMotTestByNumber', $motTest);

        $motTestOptionsDto = $this->getService()->getOptions('100013');

        $this->assertEquals($motTestOptionsDto->getMotTestStartedDate(), DateTimeApiFormat::dateTime($this->motTestStartedDate));
        $this->assertEquals($motTestOptionsDto->getVehicleMake(), $motTest->getVehicle()->getMakeName());
        $this->assertEquals($motTestOptionsDto->getVehicleModel(), $motTest->getVehicle()->getModelName());
        $this->assertEquals($motTestOptionsDto->getVehicleRegistrationNumber(), $motTest->getVehicle()->getRegistration());
        $this->assertEquals($motTestOptionsDto->getMotTestTypeDto()->getId(), $motTest->getMotTestType()->getId());
        $this->assertEquals($motTestOptionsDto->getMotTestTypeDto()->getCode(), $motTest->getMotTestType()->getCode());
    }

    private function getMockMotTestEntity()
    {
        $make = new Make();
        $make->setName('Ford');

        $model = new Model();
        $model->setName('Focus');
        $model->setMake($make);

        $modelDetail = new ModelDetail();
        $modelDetail->setModel($model);

        $vehicle = new Vehicle();
        $vehicle->setId(1)
            ->setModelDetail($modelDetail)
            ->setRegistration('8008S');

        $motTestEntity = new MotTest();

        $motTestEntity->setId(100013)
            ->setStartedDate($this->motTestStartedDate)
            ->setVehicle($vehicle)
            ->setMotTestType((new MotTestType())->setId(1)->setCode('TEST')->setIsDemo(false))
            ->setTester($this->tester);

        return $motTestEntity;
    }

    private function getService()
    {
        return new MotTestOptionsService(
            $this->motTestRepository,
            $this->readMotTestAssertion
        );
    }

    private function setMotTestRepositoryResult($method, $result)
    {
        $this->motTestRepository->expects($this->any())
            ->method($method)
            ->willReturn($result);
    }

    private function createAssertion()
    {
        return new ReadMotTestAssertion($this->authorisationService, $this->identityProvider);
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

        $identityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);
    }
}
