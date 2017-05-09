<?php

namespace SiteApiTest\Controller;

use SiteApi\Controller\SiteRoleController;

/**
 * Class SiteRoleControllerTest.
 */
class SiteRoleControllerTest extends AbstractSiteApiControllerTest
{
    public function setUp()
    {
        $this->controller = new  SiteRoleController();
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['getList']
        );
    }
}
