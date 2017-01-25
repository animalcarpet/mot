<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftCreator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dvsa\Mot\ApiClient\Service\VehicleService;

class ReplacementCertificateDraftCreatorFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ReplacementCertificateDraftCreator(
            $serviceLocator->get('MotTestSecurityService'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(VehicleService::class),
            $serviceLocator->get(EntityManager::class)
        );
    }
}
