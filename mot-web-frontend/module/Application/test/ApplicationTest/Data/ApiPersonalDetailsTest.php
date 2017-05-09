<?php

namespace ApplicationTest\Data;

use Application\Data\ApiPersonalDetails;
use DvsaCommon\HttpRestJson\Client;

/**
 * unit tests for ApiApplication.
 */
class ApiPersonalDetailsTest extends \PHPUnit_Framework_TestCase
{
    const UUID = '1111-12312332';

    /** @var $apiPersonalDetails ApiPersonalDetails */
    private $apiPersonalDetails;

    public function setUp()
    {
        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);

        $client->expects($this->any())->method('putJson')->will($this->returnValue(['data' => []]));
        $client->expects($this->any())->method('get')->will($this->returnValue(['data' => []]));

        $this->apiPersonalDetails = new ApiPersonalDetails($client);
    }

    public function test_getPersonalDetailsData()
    {
        $this->apiPersonalDetails->getPersonalDetailsData(1);
    }

    public function testupdatePersonalDetailsData()
    {
        $this->apiPersonalDetails->updatePersonalDetailsData(1, []);
    }

    public function test_updatePersonalAuthorisationForMotTesting()
    {
        $this->apiPersonalDetails->updatePersonalAuthorisationForMotTesting(1, []);
    }

    public function test_getPersonalAuthorisationForMotTesting()
    {
        $this->apiPersonalDetails->getPersonalAuthorisationForMotTesting(1);
    }
}
