<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Service\CreateMotTestService;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\TestingOutsideOpeningHoursNotificationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for MotTestService
 */
class MotTestServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new MotTestService(
            $entityManager,
            $serviceLocator->get('MotTestValidator'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('ConfigurationRepository'),
            $serviceLocator->get('MotTestMapper'),
            $serviceLocator->get(ReadMotTestAssertion::class),
            $serviceLocator->get(CreateMotTestService::class),
            $entityManager->getRepository(MotTest::class),
            $serviceLocator->get(TestingOutsideOpeningHoursNotificationService::class)
        );
    }
}
