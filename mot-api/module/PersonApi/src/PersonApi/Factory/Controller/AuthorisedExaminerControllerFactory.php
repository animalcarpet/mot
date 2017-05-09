<?php

namespace PersonApi\Factory\Controller;

use OrganisationApi\Service\AuthorisedExaminerService;
use PersonApi\Controller\AuthorisedExaminerController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class AuthorisedExaminerControllerFactory.
 *
 * Generates the AuthorisedExaminerControllerFactory, injecting dependencies
 */
class AuthorisedExaminerControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return AuthorisedExaminerController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var AuthorisedExaminerService $authorisedExaminerService */
        $authorisedExaminerService = $serviceLocator->get(AuthorisedExaminerService::class);

        return new AuthorisedExaminerController($authorisedExaminerService);
    }
}
