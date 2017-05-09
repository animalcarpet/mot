<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\Email;
use PersonApi\Service\DuplicateEmailCheckerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DuplicateEmailCheckerServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var EntityRepository $emailRepository */
        $emailRepository = $entityManager->getRepository(Email::class);

        return new DuplicateEmailCheckerService($emailRepository);
    }
}
