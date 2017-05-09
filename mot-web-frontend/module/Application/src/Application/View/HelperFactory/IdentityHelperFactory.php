<?php

namespace Application\View\HelperFactory;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Application\View\Helper\IdentityHelper;

class IdentityHelperFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();
        $identityProvider = $sl->get('MotIdentityProvider');
        $helper = new IdentityHelper($identityProvider);

        return $helper;
    }
}
