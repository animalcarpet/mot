<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\EditTelephoneController;
use PersonApi\Service\TelephoneService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class EditTelephoneControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sl = $serviceLocator->getServiceLocator();

        /** @var TelephoneService $telephoneService */
        $telephoneService = $sl->get(TelephoneService::class);

        return new EditTelephoneController($telephoneService);
    }
}
