<?php

namespace EventTest\Factory\Service;

use DvsaClient\MapperFactory;
use DvsaCommonTest\TestUtils\XMock;
use Event\Service\EventSessionService;
use Zend\Session\Container;

/**
 * Class EventSessionServiceTest.
 *
 * @group event
 */
class EventSessionServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Testing Constructor of a EventSessionService.
     */
    public function testConstructor()
    {
        $container = new Container(EventSessionService::UNIQUE_KEY);

        $service = new EventSessionService($container, XMock::of(MapperFactory::class));

        $this->assertInstanceOf(EventSessionService::class, $service);
    }
}
