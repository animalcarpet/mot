<?php

namespace Vehicle\CreateVehicle\Factory\Controller;

use Vehicle\CreateVehicle\Action\ReviewAction;
use Vehicle\CreateVehicle\Controller\ReviewController;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ReviewControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $serviceLocator = $serviceLocator->getServiceLocator();

        return new ReviewController(
            $serviceLocator->get(ReviewAction::class)
        );
    }
}