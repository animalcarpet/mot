<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\PersonEmailController;
use PersonApi\Service\DuplicateEmailCheckerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonEmailControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $service = $serviceLocator->getServiceLocator();
        $duplicateEmailChecker = $service->get(DuplicateEmailCheckerService::class);
        return new PersonEmailController($duplicateEmailChecker);
    }
}