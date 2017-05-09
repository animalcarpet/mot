<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Site;
use OrganisationApi\Service\AuthorisedExaminerStatusService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthorisedExaminerServiceFactory.
 */
class AuthorisedExaminerStatusServiceFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \OrganisationApi\Service\AuthorisedExaminerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new AuthorisedExaminerStatusService(
            $entityManager->getRepository(Site::class)
        );
    }
}
