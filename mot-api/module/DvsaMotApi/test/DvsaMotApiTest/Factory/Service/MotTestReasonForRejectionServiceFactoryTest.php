<?php

namespace DvsaMotApiTest\Factory;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Factory\Service\MotTestReasonForRejectionServiceFactory;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use DvsaMotApi\Service\TestItemSelectorService;
use DvsaMotApi\Service\Validator\MotTestValidator;
use Zend\ServiceManager\ServiceLocatorInterface;

class MotTestReasonForRejectionServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testFactory()
    {
        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), XMock::of(EntityManager::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(AuthorisationServiceInterface::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(MotTestValidator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(TestItemSelectorService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(ApiPerformMotTestAssertion::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(MotTestRepository::class));

        $this->assertInstanceOf(
            MotTestReasonForRejectionService::class,
            (new MotTestReasonForRejectionServiceFactory())->createService($mockServiceLocator)
        );
    }
}
