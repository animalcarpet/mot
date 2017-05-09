<?php

namespace EventTest\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\TestUtils\XMock;
use Event\Service\ManualEventService;

class ManualEventServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $client = XMock::of(HttpRestJsonClient::class);

        $service = new ManualEventService($client);
        $this->assertInstanceOf(ManualEventService::class, $service);
    }
}
