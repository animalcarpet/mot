<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use PersonApi\Service\PersonalDetailsService;
use PersonApi\Service\Validator\PersonalDetailsValidator;
use DvsaCommonApi\Filter\XssFilter;

/**
 * Factory for PersonalDetailsService.
 */
class PersonalDetailsServiceFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \PersonApi\Service\PersonalDetailsService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PersonalDetailsService(
            $serviceLocator->get(EntityManager::class),
            new PersonalDetailsValidator(),
            $serviceLocator->get('DvsaAuthorisationService'),
            $serviceLocator->get(MotIdentityProviderInterface::class),
            $serviceLocator->get(XssFilter::class),
            $serviceLocator->get('UserRoleService')
        );
    }
}
