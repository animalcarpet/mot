<?php

namespace DvsaMotTestTest\Factory\Controller;

use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotTest\Model\VehicleSearchResult;
use DvsaMotTest\Factory\Model\VehicleSearchResultFactory;
use Zend\Log\Logger;

/**
 * Class VehicleSearchResultFactoryTest.
 *
 * @covers \DvsaMotTest\Controller\SpecialNoticesController
 */
class VehicleSearchResultFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testVehicleSearchResultModelFactory()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(ParamObfuscator::class, XMock::of(ParamObfuscator::class));
        $logger = $this
            ->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $serviceManager->setService('Application\Logger', $logger);

        $factory = (new VehicleSearchResultFactory())->createService($serviceManager);

        $this->assertInstanceOf(VehicleSearchResult::class, $factory);
    }
}
