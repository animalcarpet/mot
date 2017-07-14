<?php

namespace DashboardTest\Controller;

use Core\Action\ViewActionResult;
use Dashboard\Action\UserStatsAction;
use DvsaCommonTest\Bootstrap;
use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dashboard\Controller\UserStatsController;
use Dvsa\Mot\Frontend\Test\StubIdentityAdapter;
use DvsaCommonTest\TestUtils\XMock;

/**
 * Tests for UserStatsController.
 */
class UserStatsControllerTest extends AbstractFrontendControllerTestCase
{
    public function setUp()
    {
        $this->setServiceManager(Bootstrap::getServiceManager());

        /** @var UserStatsAction | \PHPUnit_Framework_MockObject_MockObject */
        $userStatsAction = XMock::of(UserStatsAction::class);

        $userStatsAction->method('execute')->willReturn(new ViewActionResult());

        $this->controller = new UserStatsController(
            $userStatsAction
        );
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
