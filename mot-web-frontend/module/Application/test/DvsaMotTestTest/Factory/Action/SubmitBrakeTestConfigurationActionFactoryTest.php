<?php
namespace DvsaMotTestTest\Factory\Action;

use Core\Authorisation\Assertion\WebPerformMotTestAssertion;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use DvsaMotTest\Action\BrakeTestResults\SubmitBrakeTestConfigurationAction;
use DvsaMotTest\Factory\Action\SubmitBrakeTestConfigurationActionFactory;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaMotTest\Helper\BrakeTestConfigurationContainerHelper;
use DvsaMotTest\Mapper\BrakeTestConfigurationClass3AndAboveMapper;

class SubmitBrakeTestConfigurationActionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            SubmitBrakeTestConfigurationActionFactory::class,
            SubmitBrakeTestConfigurationAction::class,
            [
                WebPerformMotTestAssertion::class => WebPerformMotTestAssertion::class,
                'BrakeTestConfigurationContainerHelper' => BrakeTestConfigurationContainerHelper::class,
                VehicleService::class => VehicleService::class,
                MotTestService::class => MotTestService::class,
                HttpRestJsonClient::class => HttpRestJsonClient::class,
                BrakeTestConfigurationClass3AndAboveMapper::class => BrakeTestConfigurationClass3AndAboveMapper::class
            ]
        );
    }
}