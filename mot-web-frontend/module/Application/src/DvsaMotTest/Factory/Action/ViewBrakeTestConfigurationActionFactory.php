<?php

namespace DvsaMotTest\Factory\Action;

use Application\Service\ContingencySessionManager;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaMotTest\Service\BrakeTestConfigurationService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ViewBrakeTestConfigurationActionFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var BrakeTestConfigurationClass3AndAboveMapper $brakeTestConfigurationClass3AndAboveMapper */
        $brakeTestConfigurationClass3AndAboveMapper = $serviceLocator->get(BrakeTestConfigurationClass3AndAboveMapper::class);

        return new ViewBrakeTestConfigurationAction(
            $serviceLocator->get(WebPerformMotTestAssertion::class),
            $serviceLocator->get(ContingencySessionManager::class),
            $serviceLocator->get('CatalogService'),
            $serviceLocator->get(HttpRestJsonClient::class),
            $serviceLocator->get('BrakeTestConfigurationContainerHelper'),
            $serviceLocator->get(VehicleService::class),
            $serviceLocator->get(MotTestService::class),
            new BrakeTestConfigurationService($serviceLocator->get(HttpRestJsonClient::class)),
            $brakeTestConfigurationClass3AndAboveMapper
        );
    }
}
