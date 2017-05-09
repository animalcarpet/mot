<?php

namespace Application\View\HelperFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\Helper\AuthorisationHelper;
use Zend\View\HelperPluginManager;

class AuthorisationHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        if ($viewHelperServiceLocator instanceof HelperPluginManager) {
            $viewHelperServiceLocator = $viewHelperServiceLocator->getServiceLocator();
        }

        $authService = $viewHelperServiceLocator->get('AuthorisationService');

        $helper = new AuthorisationHelper($authService);

        return $helper;
    }
}
