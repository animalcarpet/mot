<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Listener\WebAuthenticationListenerFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Listener\WebAuthenticationListener;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Authentication\AuthenticationService;
use Zend\Log\LoggerInterface;

class WebAuthenticationListenerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            WebAuthenticationListenerFactory::class,
            WebAuthenticationListener::class,
            [
                'ZendAuthenticationService' => AuthenticationService::class,
                'tokenService' => TokenServiceInterface::class,
                'Application\Logger' => LoggerInterface::class,
            ]
        );
    }
}
