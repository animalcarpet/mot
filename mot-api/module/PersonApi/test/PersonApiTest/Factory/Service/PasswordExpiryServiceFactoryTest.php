<?php

namespace AccountTest\Factory\Service;

use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaEntities\Entity\PasswordDetail;
use DvsaEntities\Repository\PasswordDetailRepository;
use PersonApi\Service\PasswordExpiryNotificationService;
use PersonApi\Service\PasswordExpiryService;
use PersonApi\Factory\Service\PasswordExpiryServiceFactory;
use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\ServiceManager;

class PasswordExpiryServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceManager = new ServiceManager();

        $entityManager = XMock::of(EntityManager::class);
        $entityManager
            ->expects($this->any())
            ->method('getRepository')
            ->willReturnCallback(function ($entity) {
                switch ($entity) {
                    case PasswordDetail::class:
                        return XMock::of(PasswordDetailRepository::class);
                    default:
                        return null;
                }
            });

        $serviceManager->setService(EntityManager::class, $entityManager);
        $serviceManager->setService(PasswordExpiryNotificationService::class, XMock::of(PasswordExpiryNotificationService::class));
        $serviceManager->setService(MotConfig::class, XMock::of(MotConfig::class));
        $serviceManager->setService(MotIdentityProviderInterface::class, XMock::of(MotIdentityProviderInterface::class));

        // Create the factory
        $factory = new PasswordExpiryServiceFactory();
        $factoryResult = $factory->createService($serviceManager);

        $this->assertInstanceOf(PasswordExpiryService::class, $factoryResult);
    }
}
