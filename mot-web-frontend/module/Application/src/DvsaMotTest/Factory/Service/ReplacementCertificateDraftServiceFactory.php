<?php

namespace DvsaMotTest\Factory\Service;

use DvsaMotTest\Service\ReplacementCertificateDraftService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpClient;

class ReplacementCertificateDraftServiceFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return ReplacementCertificateDraftService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $httpClient = $serviceLocator->get(HttpClient::class);

        return new ReplacementCertificateDraftService($httpClient);
    }
}
