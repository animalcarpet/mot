<?php

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Service\OtpService;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\CertificateCreationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateService;

class ReplacementCertificateServiceFactory implements FactoryInterface
{

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ReplacementCertificateService(
            $serviceLocator->get(EntityManager::class),
            $serviceLocator->get('ReplacementCertificateDraftRepository'),
            $serviceLocator->get('ReplacementCertificateDraftCreator'),
            $serviceLocator->get('ReplacementCertificateDraftUpdater'),
            $serviceLocator->get('ReplacementCertificateUpdater'),
            $serviceLocator->get('CertificateReplacementRepository'),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(MotTestRepository::class),
            $serviceLocator->get(OtpService::class),
            $serviceLocator->get(CertificateCreationService::class)
        );
    }
}
