<?php

namespace Account\Factory\Controller;

use Account\Controller\ClaimController;
use Account\Service\ClaimAccountService;
use Account\Validator\ClaimValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ClaimAccountControllerFactory.
 */
class ClaimAccountControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new ClaimController(
            $serviceLocator->get(ClaimAccountService::class),
            new ClaimValidator(),
            $serviceLocator->get('MotIdentityProvider')
        );
    }
}
