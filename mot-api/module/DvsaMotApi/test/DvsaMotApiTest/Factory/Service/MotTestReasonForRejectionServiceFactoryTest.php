<?php

namespace DvsaMotApiTest\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestReasonForRejectionLocationRepository;
use DvsaEntities\Repository\MotTestReasonForRejectionRepository;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\ReasonForRejectionTypeRepository;
use DvsaEntities\Repository\RfrRepository;
use DvsaMotApi\Factory\Service\MotTestReasonForRejectionServiceFactory;
use DvsaMotApi\Service\Helper\BrakeTestResultsHelper;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\Validator\MotTestValidator;
use Zend\ServiceManager\ServiceLocatorInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObj;

class MotTestReasonForRejectionServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        /** @var ServiceLocatorInterface|MockObj $mockServiceLocator */
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(EntityManager::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(AuthorisationServiceInterface::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(MotTestValidator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(ApiPerformMotTestAssertion::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(MotTestRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(RfrRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(6), XMock::of(MotTestReasonForRejectionRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(7), XMock::of(MotTestReasonForRejectionLocationRepository::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(8), XMock::of(ReasonForRejectionTypeRepository::class));

        $this->assertInstanceOf(
            MotTestReasonForRejectionService::class,
            (new MotTestReasonForRejectionServiceFactory())->createService($mockServiceLocator)
        );
    }
}
