<?php

namespace DvsaMotTestTest\Data;

use DvsaCommon\HttpRestJson\Client;
use DvsaMotTest\Data\BrakeTestResultsResource;

class BrakeTestResultsResourceTest extends \PHPUnit_Framework_TestCase
{
    /** @var BrakeTestResultsResource */
    private $resource;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        $client->expects($this->once())->method('postJson')->will(
            $this->returnValue(['data' => [(int) (100 * rand())]])
        );

        $this->resource = new BrakeTestResultsResource($client);
    }

    public function testCreate()
    {
        $this->resource->save(1, []);
    }
}
