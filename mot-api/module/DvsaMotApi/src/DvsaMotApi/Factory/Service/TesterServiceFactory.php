<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Site;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaMotApi\Service\TesterService;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class TesterServiceFactory.
 */
class TesterServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new TesterService(
            $entityManager,
            $serviceLocator->get('Hydrator'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(SpecialNoticeService::class),
            $serviceLocator->get('RoleProviderService'),
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $entityManager->getRepository(Site::class)
        );
    }
}
