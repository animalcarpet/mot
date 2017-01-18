<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;
use DvsaEntities\Entity\Site;
use DvsaMotApi\Service\ReplacementCertificate\ReplacementCertificateDraftUpdater;
use DvsaMotApi\Service\Validator\ReplacementCertificateDraftChangeValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class ReplacementCertificateDraftUpdaterFactory
 */
class ReplacementCertificateDraftUpdaterFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new ReplacementCertificateDraftUpdater(
            $serviceLocator->get("MotTestSecurityService"),
            $serviceLocator->get("DvsaAuthorisationService"),
            $serviceLocator->get("VehicleCatalogService"),
            $entityManager->getRepository(CertificateChangeDifferentTesterReason::class),
            $entityManager->getRepository(Site::class),
            $serviceLocator->get('DvsaAuthenticationService'),
            $serviceLocator->get(ReplacementCertificateDraftChangeValidator::class)
        );
    }
}
