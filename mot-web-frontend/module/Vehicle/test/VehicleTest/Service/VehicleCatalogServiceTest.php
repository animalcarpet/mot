<?php

namespace VehicleTest\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\Service\VehicleCatalogService;

/**
 * Class VehicleCatalogServiceTest.
 */
class VehicleCatalogServiceTest extends \PHPUnit_Framework_TestCase
{
    private $mockRestClient;

    public function setUp()
    {
        $this->mockRestClient = XMock::of(HttpRestJsonClient::class);
    }

    public function testFindMakeWithNoParametersAndNoResponseReturnEmptyArray()
    {
        $this->mockResponseForRestClient('');

        $getMakes = $this->getService()->findMake();

        $this->assertNotFalse($getMakes);
        $this->assertCount(0, $getMakes);
    }

    public function testFindMakeWithNoParametersAndStringResponseReturnEmptyArray()
    {
        $this->mockResponseForRestClient('asdasdasdsadsad ERROR asdasdsadsa');

        $getMakes = $this->getService()->findMake();

        $this->assertNotFalse($getMakes);
        $this->assertCount(0, $getMakes);
    }

    public function testFindMakeWithNoParametersArrayReturnArray()
    {
        $this->mockResponseForRestClient($this->getMockMakeArrayResult());

        $getMakes = $this->getService()->findMake();

        $this->assertNotFalse($getMakes);
        $this->assertCount(2, $getMakes);
    }

    public function testFindModelWithNoParametersAndNoResponseReturnArray()
    {
        $this->mockResponseForRestClient('');

        $getMakes = $this->getService()->findModel();

        $this->assertNotFalse($getMakes);
        $this->assertCount(0, $getMakes);
    }

    public function testFindModelWithNoParametersAndStringResponseReturnArray()
    {
        $this->mockResponseForRestClient('asdasdasdsadsad ERROR asdasdsadsa');

        $getMakes = $this->getService()->findModel();

        $this->assertNotFalse($getMakes);
        $this->assertCount(0, $getMakes);
    }

    public function testFindModelWithNoParametersAndArrayResponseReturnArray()
    {
        $this->mockResponseForRestClient($this->getMockModelArrayResult());

        $getMakes = $this->getService()->findModel();

        $this->assertNotFalse($getMakes);
        $this->assertCount(2, $getMakes);
    }

    private function getService()
    {
        return new VehicleCatalogService(
            $this->mockRestClient
        );
    }

    private function mockResponseForRestClient($response)
    {
        $this->mockRestClient->expects($this->any())
            ->method('getWithParams')
            ->withAnyParameters()
            ->willReturn($response);
    }

    private function getMockMakeArrayResult()
    {
        return [
            'data' => [
                [
                    'id' => '1',
                    'name' => 'Ford',
                    'code' => 'F1',
                ],
                [
                    'id' => '2',
                    'name' => 'Mercedes',
                    'code' => 'M1',
                ],
            ],
        ];
    }

    private function getMockModelArrayResult()
    {
        return [
            'data' => [
                [
                    'id' => '1',
                    'name' => 'Focus',
                    'code' => 'F2',
                ],
                [
                    'id' => '2',
                    'name' => 'C 180',
                    'code' => 'M2',
                ],
            ],
        ];
    }
}
