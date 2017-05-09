<?php

namespace PersonApi\Factory\Controller;

use PersonApi\Controller\UpdateAddressController;
use PersonApi\Service\PersonAddressService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

class UpdateAddressControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /**
         * @var ServiceManager
         */
        $serviceLocator = $serviceLocator->getServiceLocator();

        /**
         * @PersonAddressService $personAddressService
         */
        $personAddressService = $serviceLocator->get(PersonAddressService::class);

        return new UpdateAddressController($personAddressService);
    }
}
