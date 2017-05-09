<?php

namespace DashboardTest;

use Application\Data\ApiPersonalDetails;
use DvsaCommon\HttpRestJson\Client;
use Dashboard\PersonStore;

/**
 * unit tests for PersonStore.
 */
class PersonStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersonStore
     */
    private $store;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        $client->expects($this->any())->method('get')->will($this->returnValue(['data' => []]));
        $client->expects($this->any())->method('putJson')->will($this->returnValue(['data' => []]));

        $apiPersonalDetails = new ApiPersonalDetails($client);

        $this->store = new PersonStore($apiPersonalDetails);
    }

    public function testGet()
    {
        $this->store->get(1);
    }

    public function testUpdate()
    {
        $this->store->update(1, []);
    }

    public function testUpdatePersonalAuthorisationForMotTesting()
    {
        $this->store->updatePersonalAuthorisationForMotTesting(1, []);
    }
}
