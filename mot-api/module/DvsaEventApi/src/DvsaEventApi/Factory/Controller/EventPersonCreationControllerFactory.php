<?php

namespace DvsaEventApi\Factory\Controller;

use DvsaEventApi\Service\EventPersonCreationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaEventApi\Controller\EventPersonCreationController;

class EventPersonCreationControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();
        /**
         * @var EventPersonCreationService
         */
        $eventPersonCreationService = $serviceLocator->get(EventPersonCreationService::class);

        return new EventPersonCreationController(
            $eventPersonCreationService
        );
    }
}
