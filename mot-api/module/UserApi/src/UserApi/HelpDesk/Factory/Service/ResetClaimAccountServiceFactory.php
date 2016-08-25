<?php

namespace UserApi\HelpDesk\Factory\Service;

use AccountApi\Service\OpenAmIdentityService;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\Person;
use DvsaEventApi\Service\EventService;
use MailerApi\Service\MailerService;
use UserApi\HelpDesk\Service\ResetClaimAccountService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Authentication\AuthenticationService;

/**
 * Class ResetClaimAccountServiceFactory
 * @package UserApi\HelpDesk\Factory\Service
 */
class ResetClaimAccountServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ResetClaimAccountService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new ResetClaimAccountService(
            $entityManager,
            $entityManager->getRepository(Person::class),
            $serviceLocator->get(MailerService::class),
            $serviceLocator->get(OpenAmIdentityService::class),
            $serviceLocator->get(EventService::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get('config'),
            new DateTimeHolder()
        );
    }
}
