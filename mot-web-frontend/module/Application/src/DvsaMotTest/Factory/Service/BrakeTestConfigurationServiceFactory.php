<?php

namespace DvsaMotTest\Factory\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BrakeTestConfigurationServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return BrakeTestConfigurationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var HttpRestJsonClient $restClient */
        $restClient = $serviceLocator->get(HttpRestJsonClient::class);

        $authorisedClassesService = new BrakeTestConfigurationService($restClient);

        return $authorisedClassesService;
    }

}