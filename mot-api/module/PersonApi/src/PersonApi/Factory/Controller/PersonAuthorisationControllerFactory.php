<?php

namespace PersonApi\Factory\Controller;

use DvsaAuthorisation\Service\AuthorisationService;
use PersonApi\Controller\PersonAuthorisationController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonAuthorisationControllerFactory.
 *
 * Generates the PersonAuthorisationController, injecting dependencies
 */
class PersonAuthorisationControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonAuthorisationController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var AuthorisationService $authorisationService */
        $authorisationService = $serviceLocator->get('DvsaAuthorisationService');

        return new PersonAuthorisationController($authorisationService);
    }
}
