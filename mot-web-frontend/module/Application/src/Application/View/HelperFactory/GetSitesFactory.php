<?php

namespace Application\View\HelperFactory;

use Application\View\Helper\GetSites;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class GetSitesFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();
        $manager = $sl->get('LoggedInUserManager');

        $helper = new GetSites($manager);

        return $helper;
    }
}
