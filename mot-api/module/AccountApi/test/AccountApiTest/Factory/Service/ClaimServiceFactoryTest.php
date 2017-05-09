<?php

namespace AccountApiTest\Factory\Service;

use AccountApi\Factory\Service\ClaimServiceFactory;
use AccountApi\Service\ClaimService;
use AccountApi\Service\OpenAmIdentityService;
use AccountApi\Service\Validator\ClaimValidator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\SecurityQuestionRepository;
use DvsaEventApi\Service\EventService;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ClaimServiceFactoryTest.
 */
class ClaimServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testEventServiceGetList()
    {
        $entityManager = XMock::of(EntityManager::class);
        $this->mockMethod($entityManager, 'getRepository', $this->at(0), XMock::of(SecurityQuestionRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(1), XMock::of(PersonRepository::class));
        $this->mockMethod($entityManager, 'getRepository', $this->at(2), XMock::of(EntityRepository::class));

        $mockServiceLocator = XMock::of(ServiceLocatorInterface::class, ['get']);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(0), $entityManager);
        $this->mockMethod($mockServiceLocator, 'get', $this->at(1), XMock::of(MotIdentityProviderInterface::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(2), XMock::of(ClaimValidator::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(3), XMock::of(OpenAmIdentityService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(4), XMock::of(EventService::class));
        $this->mockMethod($mockServiceLocator, 'get', $this->at(5), XMock::of(ParamObfuscator::class));

        $this->assertInstanceOf(
            ClaimService::class,
            (new ClaimServiceFactory())->createService($mockServiceLocator)
        );
    }
}
