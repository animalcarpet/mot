<?php

namespace DvsaMotTest\Factory\Mapper;

use DvsaFeature\FeatureToggles;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Specification\OfficialWeightSourceForVehicle;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class BrakeTestConfigurationClass3AndAboveMapperFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var FeatureToggles $featureToggles */
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');
        /** @var OfficialWeightSourceForVehicle $officialWeightSourceForVehicleSpec */
        $officialWeightSourceForVehicleSpec = $serviceLocator->get(OfficialWeightSourceForVehicle::class);

        return new BrakeTestConfigurationClass3AndAboveMapper(
            $featureToggles,
            $officialWeightSourceForVehicleSpec
        );
    }
}