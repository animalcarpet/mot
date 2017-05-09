<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\IdentitySessionStateServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\IdentitySessionStateService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Log\LoggerInterface;

class IdentitySessionStateServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            IdentitySessionStateServiceFactory::class,
            IdentitySessionStateService::class,
            [
                'MotIdentityProvider' => MotIdentityProviderInterface::class,
                OpenAMClientInterface::class,
                'tokenService' => WebAuthenticationCookieService::class,
                'Application/Logger' => LoggerInterface::class,
            ]
        );
    }
}
