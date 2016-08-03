<?php

namespace DvsaMotApi\Factory\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateUpdater;
use Dvsa\Mot\ApiClient\Service\VehicleService;

class ReplacementCertificateUpdaterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ReplacementCertificateUpdater(
            $serviceLocator->get('MotTestSecurityService'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('DvsaAuthenticationService'),
            $serviceLocator->get(VehicleService::class)
        );
    }
}
