<?php

namespace DvsaMotApi\Factory;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\CertificateReplacementDraft;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReplacementCertificateDraftRepositoryFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $serviceLocator->get(EntityManager::class)->getRepository(CertificateReplacementDraft::class);
    }
}
