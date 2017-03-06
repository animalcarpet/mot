<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot
 */

namespace DvsaMotApiTest\Factory\Helper;

use Dvsa\Mot\ApiClient\Service\VehicleService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Factory\Helper\MysteryShopperHelperFactory;
use DvsaMotApi\Helper\MysteryShopperHelper;

/**
 * Class MysteryShopperHelperFactoryTest.
 */
class MysteryShopperHelperFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testMysteryShopperHelperFactory()
    {
        $serviceManager = Bootstrap::getServiceManager();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService(VehicleService::class, XMock::of(VehicleService::class));
        $serviceManager->setService(MotAuthorisationServiceInterface::class, XMock::of(MotAuthorisationServiceInterface::class));
        $serviceManager->setService(Logger::class, XMock::of(Logger::class));
        $mysteryShopperHelper = (new MysteryShopperHelperFactory())->createService($serviceManager);

        $this->assertInstanceOf(MysteryShopperHelper::class, $mysteryShopperHelper);
    }
}
