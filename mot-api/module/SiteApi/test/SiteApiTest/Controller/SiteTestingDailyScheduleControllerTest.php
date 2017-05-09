<?php

namespace SiteApiTest\Controller;

use SiteApi\Controller\SiteTestingDailyScheduleController;

/**
 * Class SiteTestingDailyScheduleControllerTest.
 */
class SiteTestingDailyScheduleControllerTest extends AbstractSiteApiControllerTest
{
    public function setUp()
    {
        $this->controller = new  SiteTestingDailyScheduleController();
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['get', 'update']
        );
    }
}
