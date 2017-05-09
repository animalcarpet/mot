<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Listener\Factory;

use Dvsa\OpenAM\Options\OpenAMClientOptions;
use Dvsa\Mot\Frontend\AuthenticationModule\Factory\Service\WebAuthenticationCookieServiceFactory;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService;
use DvsaCommonTest\TestUtils\ServiceFactoryTestHelper;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\Response;

class WebAuthenticationCookieServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryCreatesInstance()
    {
        ServiceFactoryTestHelper::testCreateServiceForSM(
            WebAuthenticationCookieServiceFactory::class,
            WebAuthenticationCookieService::class,
            [
                OpenAMClientOptions::class,
                'Request' => Request::class,
                'Response' => Response::class,
            ]
        );
    }
}
