<?php

namespace DvsaMotTest\Factory\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Service\CertificatePrintingService;

class CertificatePrintingServiceFactory implements FactoryInterface
{
    /**
     * Create CertificatePrintingService.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return CertificatePrintingService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $restClient = $serviceLocator->get(HttpRestJsonClient::class);
        $service = new CertificatePrintingService($restClient);

        return $service;
    }
}
