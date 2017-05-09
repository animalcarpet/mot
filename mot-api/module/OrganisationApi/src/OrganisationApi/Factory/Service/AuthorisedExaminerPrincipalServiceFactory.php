<?php

namespace OrganisationApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\AuthorisedExaminerPrincipal;
use DvsaEventApi\Service\EventService;
use OrganisationApi\Service\AuthorisedExaminerPrincipalService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use OrganisationApi\Service\Validator\AuthorisedExaminerPrincipalValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class AuthorisedExaminerPrincipalServiceFactory.
 */
class AuthorisedExaminerPrincipalServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new AuthorisedExaminerPrincipalService(
            $serviceLocator->get(EntityManager::class)->getRepository(Organisation::class),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(EventService::class),
            $serviceLocator->get(EntityManager::class)->getRepository(AuthorisedExaminerPrincipal::class),
            $serviceLocator->get(MotIdentityProviderInterface::class),
            new AuthorisedExaminerPrincipalValidator()
        );
    }
}
