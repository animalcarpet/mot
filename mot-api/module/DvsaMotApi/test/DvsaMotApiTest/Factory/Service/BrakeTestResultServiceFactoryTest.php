<?php

namespace DvsaMotApiTest\Factory;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BrakeTestType;
use DvsaEntities\Entity\WeightSource;
use DvsaEntities\Repository\BrakeTestTypeRepository;
use DvsaEntities\Repository\WeightSourceRepository;
use DvsaFeature\FeatureToggles;
use DvsaMotApi\Factory\Service\BrakeTestResultServiceFactory;
use DvsaMotApi\Service\BrakeTestResultService;
use DvsaMotApi\Service\Calculator\BrakeTestResultClass3AndAboveCalculator;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\Validator\BrakeTestConfigurationValidator;
use DvsaMotApi\Service\Validator\BrakeTestResultValidator;
use DvsaMotApi\Service\Validator\MotTestValidator;
use Zend\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class BrakeTestResultServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var ServiceLocatorInterface|MockObject $serviceLocator */
    private $serviceLocator;

    public function setUp()
    {
        $this->serviceLocator = XMock::of(ServiceLocatorInterface::class);

        $this->setupMethodExpectation(0, EntityManager::class, $this->getEntityManagerMock());
        $this->setupMethodExpectation(1, 'BrakeTestResultValidator', BrakeTestResultValidator::class);
        $this->setupMethodExpectation(2, 'BrakeTestConfigurationValidator', BrakeTestConfigurationValidator::class);
        $this->setupMethodExpectation(3, 'Hydrator', DoctrineObject::class);
        $this->setupMethodExpectation(4, 'DvsaAuthorisationService', AuthorisationServiceInterface::class);
        $this->setupMethodExpectation(5, 'MotTestValidator', MotTestValidator::class);
        $this->setupMethodExpectation(6, MotTestReasonForRejectionService::class, MotTestReasonForRejectionService::class);
        $this->setupMethodExpectation(7, ApiPerformMotTestAssertion::class, ApiPerformMotTestAssertion::class);
        $this->setupMethodExpectation(8, 'Feature\FeatureToggles', FeatureToggles::class);
        $this->setupMethodExpectation(9, BrakeTestResultClass3AndAboveCalculator::class, BrakeTestResultClass3AndAboveCalculator::class);
    }

    public function testFactory()
    {
        $factory = new BrakeTestResultServiceFactory();
        $result = $factory->createService($this->serviceLocator);

        $this->assertInstanceOf(BrakeTestResultService::class, $result);
    }

    private function setupMethodExpectation($invocationIndex, $serviceName, $returnValue)
    {
        $this->serviceLocator
            ->expects($this->at($invocationIndex))
            ->method('get')
            ->with($serviceName)
            ->willReturn(is_object($returnValue) ? $returnValue : XMock::of($returnValue));
    }

    private function getEntityManagerMock()
    {
        $emMock = XMock::of(EntityManager::class);

        $brakeTestTypeRepoMock = XMock::of(BrakeTestTypeRepository::class);
        $weightSourceRepoMock = XMock::of(WeightSourceRepository::class);

        $emMock
            ->expects($this->at(0))
            ->method('getRepository')
            ->with(BrakeTestType::class)
            ->willReturn($brakeTestTypeRepoMock);


        $emMock
            ->expects($this->at(1))
            ->method('getRepository')
            ->with(WeightSource::class)
            ->willReturn($weightSourceRepoMock);

        return $emMock;
    }

}