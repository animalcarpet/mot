<?php

namespace DvsaMotTest\Factory\Action;

use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Action\BrakeTestResults\SubmitBrakeTestConfigurationAction;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class SubmitBrakeTestConfigurationActionFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return SubmitBrakeTestConfigurationAction
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var BrakeTestConfigurationClass3AndAboveMapper $brakeTestConfigurationClass3AndAboveMapper */
        $brakeTestConfigurationClass3AndAboveMapper = $serviceLocator->get(BrakeTestConfigurationClass3AndAboveMapper::class);

        return new SubmitBrakeTestConfigurationAction(
            $serviceLocator->get(WebPerformMotTestAssertion::class),
            $serviceLocator->get('BrakeTestConfigurationContainerHelper'),
            $serviceLocator->get(VehicleService::class),
            $serviceLocator->get(MotTestService::class),
            new BrakeTestConfigurationService($serviceLocator->get(HttpRestJsonClient::class)),
            $brakeTestConfigurationClass3AndAboveMapper
        );
    }
}
