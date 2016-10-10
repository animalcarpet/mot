<?php


namespace UserAdmin\Factory\Service;


use UserAdmin\Service\IsEmailDuplicateService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class IsEmailDuplicateServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $client = $serviceLocator->get(HttpRestJsonClient::class);

        return new IsEmailDuplicateService($client);
    }
}