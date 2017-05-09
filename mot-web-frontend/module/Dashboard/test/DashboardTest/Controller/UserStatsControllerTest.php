<?php

namespace DashboardTest\Controller;

use DvsaCommonTest\Bootstrap;
//use DvsaCommonTest\Controller\AbstractAuthControllerTestCase;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dashboard\Controller\UserStatsController;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;

/**
 * Tests for UserStatsController.
 */
class UserStatsControllerTest extends AbstractFrontendControllerTestCase
{
    public function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());
        $this->controller = new UserStatsController();
        parent::setUp();
    }

    public function testShowActionCanBeAccessed()
    {
        $this->setupAuthenticationServiceForIdentity(StubIdentityAdapter::asTester());

        $this->getRestClientMockForServiceManager();

        $response = $this->getResponseForAction('show');

        $this->assertEquals(self::HTTP_OK_CODE, $response->getStatusCode());
    }
}
