<?php

namespace VehicleTest\Service;

use Core\Helper\GoogleAnalyticsHelper;

class GoogleAnalyticsHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testGetGaTrackingCode()
    {
        $code = 'xxxxx';
        $helper = new GoogleAnalyticsHelper($code);
        $this->assertEquals($code, $helper->getGATrackingCode());
    }
}
