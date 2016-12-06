<?php

namespace VehicleTest\CreateVehicle\Service;

use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\CreateVehicle\Form\MakeForm;
use Vehicle\CreateVehicle\Service\CreateVehicleModelService;
use DvsaCommon\HttpRestJson\Client;
use Vehicle\CreateVehicle\Service\CreateVehicleStepService;

class CreateVehicleModelServiceTest extends \PHPUnit_Framework_TestCase
{
    private $createVehicleStepService;
    private $client;

    public function setUp()
    {
        $this->createVehicleStepService = XMock::of(CreateVehicleStepService::class);
        $this->client = XMock::of(Client::class);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Make not set in Session
     */
    public function testWhenMakeNotSetInSession_ExceptionThrown()
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MAKE_STEP)
            ->willReturn(null);

        $this->client
            ->expects($this->never())
            ->method('get');

        $this->buildService()->getModelFromMakeInSession();
    }

    public function testWhenMakeIsOther_NoModelsReturned()
    {
        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MAKE_STEP)
            ->willReturn([MakeForm::MODEL => MakeForm::OTHER]);

        $this->client
            ->expects($this->never())
            ->method('get');

        $actual = $this->buildService()->getModelFromMakeInSession();
        $this->assertEmpty($actual);
    }

    public function testWhenMakeIsSetInSessionAndNotOther_ModelsFromApiAreReturned()
    {
        $make = '12345';
        $apiResponse = ['A1', 'A2', 'A3'];

        $this->createVehicleStepService
            ->expects($this->once())
            ->method('getStep')
            ->with(CreateVehicleStepService::MAKE_STEP)
            ->willReturn([MakeForm::MODEL => $make ]);

        $this->client
            ->expects($this->once())
            ->method('get')
            ->with(UrlBuilder::vehicleDictionary()->make($make)->models()->toString())
            ->willReturn(['data' => $apiResponse]);

        $actual = $this->buildService()->getModelFromMakeInSession();
        $this->assertEquals($apiResponse, $actual);
    }

    private function buildService()
    {
        return new CreateVehicleModelService(
            $this->createVehicleStepService,
            $this->client
        );
    }
}