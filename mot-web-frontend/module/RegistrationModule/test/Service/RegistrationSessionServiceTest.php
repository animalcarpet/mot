<?php

namespace Dvsa\Mot\Frontend\RegistrationModuleTest\Factory\Service;

use Dvsa\Mot\Frontend\RegistrationModule\Service\RegistrationSessionService;
use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Session\Container;
use Zend\Session\SessionManager;
use Zend\Session\Storage\SessionStorage;

/**
 * Class RegistrationSessionServiceTest.
 *
 * @group VM-11506
 */
class RegistrationSessionServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing Constructor of a RegistrationSessionService.
     */
    public function testConstructor()
    {
        $container = new Container(RegistrationSessionService::UNIQUE_KEY);

        $service = new RegistrationSessionService($container, XMock::of(MapperFactory::class));

        $this->assertInstanceOf(RegistrationSessionService::class, $service);
    }
}
