<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonProfileUnrestrictedController;
use UserApi\HelpDesk\Service\HelpDeskPersonService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class PersonProfileUnrestrictedControllerFactory.
 *
 * Generates the PersonProfileUnrestrictedController, injecting dependencies
 */
class PersonProfileUnrestrictedControllerFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return PersonProfileUnrestrictedController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var HelpDeskPersonService $helpDeskPersonService */
        $helpDeskPersonService = $serviceLocator->get(HelpDeskPersonService::class);

        return new PersonProfileUnrestrictedController($helpDeskPersonService);
    }
}
