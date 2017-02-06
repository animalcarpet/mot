<?php

namespace DvsaMotApi\Factory\Service;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateUpdater;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReplacementCertificateUpdaterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ReplacementCertificateUpdater(
            $serviceLocator->get('MotTestSecurityService'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('DvsaAuthenticationService'),
            $serviceLocator->get(VehicleService::class),
            $serviceLocator->get(MotTestRepository::class)
        );
    }
}
