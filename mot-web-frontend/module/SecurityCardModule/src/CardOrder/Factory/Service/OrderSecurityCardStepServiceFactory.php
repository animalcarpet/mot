<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Zend\InputFilter\InputFilter;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OrderSecurityCardStepServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return OrderSecurityCardStepService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var OrderNewSecurityCardSessionService $sessionService */
        $sessionService = $serviceLocator->get(OrderNewSecurityCardSessionService::class);

        return new OrderSecurityCardStepService($sessionService);
    }
}
