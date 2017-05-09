<?php

namespace Application\View\HelperFactory;

use Application\View\Helper\DashboardDataProvider;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Dashboard\Data\ApiDashboardResource;

class DashboardDataProviderFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $viewHelperServiceLocator)
    {
        $sl = $viewHelperServiceLocator->getServiceLocator();

        $identityProvider = $sl->get('MotIdentityProvider');
        $apiService = $sl->get(ApiDashboardResource::class);
        $authService = $sl->get('AuthorisationService');

        $helper = new DashboardDataProvider($identityProvider, $apiService, $authService);

        return $helper;
    }
}
