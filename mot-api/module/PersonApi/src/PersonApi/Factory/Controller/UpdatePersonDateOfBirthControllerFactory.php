<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\UpdatePersonDateOfBirthController;
use PersonApi\Service\PersonDateOfBirthService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class UpdatePersonDateOfBirthControllerFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $controllerManager
     *
     * @return UpdatePersonDateOfBirthController
     */
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();
        /** @var PersonDateOfBirthService $personDayOfBirthService */
        $personDayOfBirthService = $serviceLocator->get(PersonDateOfBirthService::class);

        return new UpdatePersonDateOfBirthController($personDayOfBirthService);
    }
}
