<?php

namespace DvsaMotTest\Factory\Service;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Service\VehicleSearchService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use Application\Service\ContingencySessionManager;

/**
 * Class VehicleSearchServiceFactory.
 */
class VehicleSearchServiceFactory implements FactoryInterface
{
    /**
     * Create VehicleSearchService.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VehicleSearchService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $restClient = $serviceLocator->get(HttpRestJsonClient::class);
        $paramObfuscator = $serviceLocator->get(ParamObfuscator::class);
        $contingencySessionManager = $serviceLocator->get(ContingencySessionManager::class);
        $vehicleSearchResultModel = $serviceLocator->get(VehicleSearchResult::class);
        $dataCatalogService = $serviceLocator->get('CatalogService');
        $authorisationService = $serviceLocator->get('AuthorisationService');

        $authorisedClassesService = new VehicleSearchService(
            $restClient,
            $paramObfuscator,
            $contingencySessionManager,
            $vehicleSearchResultModel,
            $dataCatalogService,
            $authorisationService
        );

        return $authorisedClassesService;
    }
}
