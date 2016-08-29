<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
namespace Event\Factory\Service;

use DvsaClient\MapperFactory;
use Event\Service\EventSessionService;
use Zend\Session\Container;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class EventSessionServiceFactory.
 */
class EventSessionServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return EventSessionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sessionContainer = new Container(EventSessionService::UNIQUE_KEY);

        return new EventSessionService(
            $sessionContainer,
            $serviceLocator->get(MapperFactory::class)
        );
    }
}
