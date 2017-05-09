<?php

namespace DvsaMotTest\Factory\Model;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Constants\VehicleSearchSource;
use DvsaCommon\Obfuscate\ParamObfuscator;

/**
 * Class VehicleSearchResultFactory.
 */
class VehicleSearchResultFactory implements FactoryInterface
{
    /**
     * @TODO need to add in the CatalogService
     * Create VehicleSearchResultFactory
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return VehicleSearchResult
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $vehicleSearchResult = new VehicleSearchResult(
            $serviceLocator->get(ParamObfuscator::class),
            new VehicleSearchSource(),
            $serviceLocator->get('Application\Logger')
        );

        return $vehicleSearchResult;
    }
}
