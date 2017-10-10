<?php

namespace Dashboard\Factory\Controller;

use Dashboard\Controller\PasswordController;
use Dashboard\Form\ChangePasswordForm;
use Dashboard\Service\PasswordService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaCommon\Configuration\MotConfig;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

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
            ),
            $serviceLocator->get('MotIdentityProvider'),
            $serviceLocator->get(MotConfig::class),
            $serviceLocator->get(WebLogoutService::class)
        );
    }
}
