<?php

namespace Application\View\HelperFactory;

use Application\Data\ApiCurrentMotTest;
use Application\View\Helper\CurrentMotTest;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class CurrentMotTestFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();

        $identityProvider = $sl->get('MotIdentityProvider');
        $apiService = $sl->get(ApiCurrentMotTest::class);

        $helper = new CurrentMotTest($identityProvider, $apiService);

        return $helper;
    }
}
