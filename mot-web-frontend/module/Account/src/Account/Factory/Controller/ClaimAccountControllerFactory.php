<?php

namespace Account\Factory\Controller;

use Account\Controller\ClaimController;
use Account\Service\ClaimAccountService;
use Account\Validator\ClaimValidator;
use DvsaCommon\Auth\MotIdentityProvider;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ClaimAccountControllerFactory
 *
 * @package Account\Factory\Controller
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
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get('config')
        );
    }
}
