<?php

namespace DashboardTest\Data;

use DvsaCommon\HttpRestJson\Client;
use Dashboard\Data\ApiNotificationResource;

/**
 * Class ApiNotificationResourceTest
 *
 * @package DashboardTest\Data
 */
class ApiNotificationResourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ApiNotificationResource
     */
    private $resource;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        $this->resource = new ApiNotificationResource($client);
    }

    public function testGet()
    {
        $this->resource->get(1);
    }

    public function testArchive()
    {
        $this->resource->archive(1);
    }

    public function testGetList()
    {
        $this->resource->getInboxNotifications(1);
    }

    public function testMarkAsRead()
    {
        $this->resource->markAsRead(1);
    }

    public function testNotificationAction()
    {
        $this->resource->notificationAction(1, 1, 'reject');
    }
}
