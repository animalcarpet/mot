<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Validator\PersonNameValidator;
use DvsaCommonApi\Filter\XssFilter;
use PersonApi\Service\PersonNameService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonNameServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PersonNameService(
            $serviceLocator->get(EntityManager::class),
            new PersonNameValidator(),
            $serviceLocator->get(XssFilter::class),
            $serviceLocator->get('DvsaAuthorisationService')
        );
    }
}
