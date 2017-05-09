<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\WebLogoutServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Log\LoggerInterface;
use Zend\Session\SessionManager;

class WebLogoutServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            WebLogoutServiceFactory::class,
            WebLogoutService::class,
            [
                OpenAMClientInterface::class,
                'tokenService' => WebAuthenticationCookieService::class,
                'Application\Logger' => LoggerInterface::class,
                'Zend\Session\SessionManager' => SessionManager::class,
            ]
        );
    }
}
