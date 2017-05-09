<?php

namespace DvsaMotTestTest\Factory\Controller;

use Application\Service\CatalogService;
use Application\Service\ContingencySessionManager;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Service\VehicleSearchService;
use DvsaMotTest\Factory\Service\VehicleSearchServiceFactory;
use Core\Service\LazyMotFrontendAuthorisationService;

/**
 * Class VehicleSearchServiceFactoryTest.
 *
 * @covers \DvsaMotTest\Factory\Service\VehicleSearchServiceFactory
 */
class VehicleSearchServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testVehicleSearchServiceFactory()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);

        $serviceManager->setService(ParamObfuscator::class, XMock::of(ParamObfuscator::class));
        $serviceManager->setService(ContingencySessionManager::class, XMock::of(ContingencySessionManager::class));
        $serviceManager->setService(HttpRestJsonClient::class, XMock::of(HttpRestJsonClient::class));
        $serviceManager->setService(VehicleSearchResult::class, XMock::of(VehicleSearchResult::class));
        $serviceManager->setService(CatalogService::class, XMock::of(CatalogService::class));
        $serviceManager->setService('AuthorisationService', XMock::of(LazyMotFrontendAuthorisationService::class));

        $factory = (new VehicleSearchServiceFactory())->createService($serviceManager);

        $this->assertInstanceOf(VehicleSearchService::class, $factory);
    }
}
