<?php
namespace DvsaMotTestTest\Factory\Action;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaFeature\FeatureToggles;
use DvsaMotTest\Action\BrakeTestResults\ViewBrakeTestConfigurationAction;
use DvsaMotTest\Factory\Action\ViewBrakeTestConfigurationActionFactory;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class ViewBrakeTestConfigurationActionFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function testFactory()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            ViewBrakeTestConfigurationActionFactory::class,
            ViewBrakeTestConfigurationAction::class,
            [
                WebPerformMotTestAssertion::class => WebPerformMotTestAssertion::class,
                ContingencySessionManager::class => ContingencySessionManager::class,
                'CatalogService' => CatalogService::class,
                'BrakeTestConfigurationContainerHelper' => BrakeTestConfigurationContainerHelper::class,
                VehicleService::class => VehicleService::class,
                MotTestService::class => MotTestService::class,
                HttpRestJsonClient::class => HttpRestJsonClient::class,
                BrakeTestConfigurationClass3AndAboveMapper::class => BrakeTestConfigurationClass3AndAboveMapper::class,
                'Feature\FeatureToggles' => FeatureToggles::class
            ]
        );
    }

}