<?php

namespace DvsaClientTest\Mapper;

use DvsaClient\Mapper\SitePositionMapper;

/**
 * Class SitePositionMapperTest.
 */
class SitePositionMapperTest extends AbstractMapperTest
{
    /** @var $mapper SitePositionMapper */
    private $mapper;

    public function setUp()
    {
        parent::setUp();

        $this->mapper = new SitePositionMapper($this->client);
    }

    public function testPost()
    {
        $this->mapper->post(1, 1, 1);
    }

    public function testUpdate()
    {
        $expectedResponse = ['data' => ['id' => 999]];

        $this->client->expects($this->once())
            ->method('put')
            ->willReturn($expectedResponse);

        $this->assertSame(
            $expectedResponse,
            $this->mapper->update(1, 1, 'SITE-MANAGER')
        );
    }

    public function testDelete()
    {
        $this->mapper->deletePosition(1, 1);
    }

    public function testGetList()
    {
        $this->mapper->validate(1, 1, 1);
    }
}
