<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Factory\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service\LostOrForgottenSessionService;
use DvsaClient\MapperFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class LostOrForgottenSessionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return LostOrForgottenSessionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer =  new Container(LostOrForgottenSessionService::UNIQUE_KEY);
        $mapperFactory = $serviceLocator->get(MapperFactory::class);

        return new LostOrForgottenSessionService(
            $sessionContainer,
            $mapperFactory
        );
    }
}
