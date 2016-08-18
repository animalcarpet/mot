<?php

namespace DvsaMotTest\Factory\Service;

use DvsaCommon\HttpRestJson\Client;
use DvsaMotTest\Service\SurveyService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SurveyServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var Client $client */
        $client = $serviceLocator->get(Client::class);

        return new SurveyService($client);
    }
}
