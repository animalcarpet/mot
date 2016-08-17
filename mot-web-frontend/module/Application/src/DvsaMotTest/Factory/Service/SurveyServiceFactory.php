<?php

namespace DvsaMotTest\Factory\Service;

use Core\Service\SessionService;
use DvsaClient\MapperFactory;
use DvsaCommon\HttpRestJson\Client;
use DvsaMotTest\Service\SurveyService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Container;

class SurveyServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Client $client */
        $client = $serviceLocator->get(Client::class);

        /**
         * @var MapperFactory $mapper
         */
        $mapper = $serviceLocator->get(MapperFactory::class);

        /** @var SessionService $sessionService */
        $sessionService = new SessionService(
            (new Container(SessionService::UNIQUE_KEY)), $mapper
        );

        return new SurveyService($client, $sessionService);
    }
}
