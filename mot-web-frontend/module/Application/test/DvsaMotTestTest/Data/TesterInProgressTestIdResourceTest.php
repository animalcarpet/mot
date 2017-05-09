<?php

namespace DvsaMotTestTest\Data;

use DvsaCommon\HttpRestJson\Client;
use DvsaMotTest\Data\TesterInProgressTestNumberResource;

class TesterInProgressTestIdResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var TesterInProgressTestNumberResource */
    private $resource;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        // get should be called only once and  cached
        $client->expects($this->once())->method('get')->will($this->returnValue(['data' => [(int) (100 * rand())]]));

        $this->resource = new TesterInProgressTestNumberResource($client);
    }

    public function testGet()
    {
        $personId = 42;
        $this->resource->get($personId);
    }
}
