<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;

class LogoutControllerTest  extends AbstractLightWebControllerTest
{
    /**
     * @var WebLogoutService
     */
    private $logoutService;

    public function setUp()
    {
        parent::setUp();

        $this->logoutService = $this
            ->getMockBuilder(WebLogoutService::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testRedirect()
    {
        $this->setController(new LogoutController($this->logoutService));

        $this->expectRedirect('login');
        $this->getController()->logoutAction();
    }
}
