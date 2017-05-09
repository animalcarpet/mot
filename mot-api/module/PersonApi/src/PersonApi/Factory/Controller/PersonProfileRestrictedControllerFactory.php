<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonProfileRestrictedController;
use UserApi\HelpDesk\Service\HelpDeskPersonService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonProfileRestrictedControllerFactory.
 *
 * Generates the PersonProfileRestrictedController, injecting dependencies
 */
class PersonProfileRestrictedControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonProfileRestrictedController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var HelpDeskPersonService $helpDeskPersonService */
        $helpDeskPersonService = $serviceLocator->get(HelpDeskPersonService::class);

        return new PersonProfileRestrictedController($helpDeskPersonService);
    }
}
