<?php

namespace Dashboard\Factory\Controller;

use Dashboard\Controller\PasswordController;
use Dashboard\Service\PasswordService;
use Dashboard\Form\ChangePasswordForm;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;

class PasswordControllerFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $controllerManager)
    {
        /** @var ServiceManager $serviceLocator */
        $serviceLocator = $controllerManager->getServiceLocator();

        return new PasswordController(
            $serviceLocator->get(PasswordService::class),
            new ChangePasswordForm(
                $serviceLocator->get('MotIdentityProvider'),
                $serviceLocator->get(OpenAMClientInterface::class),
                $serviceLocator->get(OpenAMClientOptions::class)->getRealm()
                )
            );
    }
}
