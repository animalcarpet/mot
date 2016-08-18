<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\Mot\Frontend\AuthenticationModule\Controller\LogoutController;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Controller\LogoutControllerFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;

class LogoutControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testLogoutControllerInstanceCreation()
    {
        ServiceFactoryTestHelper::testCreateServiceForCM(
            LogoutControllerFactory::class,
            LogoutController::class,
            [
                WebLogoutService::class,
            ]
        );
    }
}
