<?php

namespace PersonApiTest\Controller;

use PersonApi\Controller\PersonCurrentMotTestController;
use PersonApi\Service\PersonService;

/**
 * Unit tests for PersonController.
 */
class PersonCurrentMotTestControllerTest extends AbstractPersonControllerTestCase
{
    protected $mockPersonService;

    public function setUp()
    {
        $mockPersonServiceMethods = [
            'getCurrentMotTestIdByPersonId',
        ];

        $this->mockPersonService = $this->getMockBuilder(PersonService::class)
            ->setMethods($mockPersonServiceMethods)
            ->disableOriginalConstructor()
            ->getMock();

        $this->controller = new PersonCurrentMotTestController($this->mockPersonService);
        $this->setUpTestCase();
    }

    public function testWhiteList()
    {
        $this->assertMethodsOk(
            ['get']
        );
    }

    public function testReturnsMotNumber()
    {
        $motNumber = 123456;

        $this->mockPersonService->expects($this->once())
            ->method('getCurrentMotTestIdByPersonId')
            ->will($this->returnValue(['inProgressTestNumber' => $motNumber]));

        $viewModel = $this->controller->get(1);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($motNumber, $viewModel->getVariable('data')['inProgressTestNumber']);
    }

    public function testReturnsNull()
    {
        $motNumber = null;

        $this->mockPersonService->expects($this->once())
            ->method('getCurrentMotTestIdByPersonId')
            ->will($this->returnValue(['inProgressTestNumber' => $motNumber]));

        $viewModel = $this->controller->get(1);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($motNumber, $viewModel->getVariable('data')['inProgressTestNumber']);
    }
}
