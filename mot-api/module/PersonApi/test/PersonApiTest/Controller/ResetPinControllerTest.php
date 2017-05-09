<?php

namespace PersonApiTest\Controller;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use PersonApi\Controller\ResetPinController;
use PersonApi\Service\PersonService;

class ResetPinControllerTest extends AbstractPersonControllerTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPersonService;

    public function setUp()
    {
        $mockPersonServiceMethods = [
            'regeneratePinForPerson',
            'validateCredentials',
        ];

        $this->mockPersonService = $this->getMockBuilder(PersonService::class)
                                        ->setMethods($mockPersonServiceMethods)
                                        ->disableOriginalConstructor()
                                        ->getMock();

        $this->controller = new ResetPinController($this->mockPersonService);
        $this->setUpTestCase();
    }

    /**
     * The person's PIN can only be updated when requested with a token of same person (i.e. user can only update
     * their own PIN).
     */
    public function testPinNotUpdateableByOther()
    {
        $identity = $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedPin = 123456;

        $viewModel = $this->controller->update(($identity->getUserId() + 1), []);
        $response = $this->controller->getResponse();

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Can only reset your own PIN', $viewModel->getVariable('errors')['message']);
    }

    /**
     * The person's PIN can only be updated when requested with a token of same person (i.e. user can only update
     * their own PIN).
     */
    public function testPinUpdateableOnlyBySelf()
    {
        $identity = $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedPin = 123456;

        $this->mockPersonService->expects($this->once())
            ->method('regeneratePinForPerson')
            ->will($this->returnValue($expectedPin));

        $viewModel = $this->controller->update($identity->getUserId(), []);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedPin, $viewModel->getVariable('data')['pin']);
    }

    /**
     * A 200 should present us with a pin value.
     */
    public function testNewPinReturnedWhenValidRequestMade()
    {
        $identity = $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $expectedPin = 123456;

        $this->mockPersonService->expects($this->once())
            ->method('regeneratePinForPerson')
            ->will($this->returnValue($expectedPin));

        $viewModel = $this->controller->update($identity->getUserId(), []);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals($expectedPin, $viewModel->getVariable('data')['pin']);
    }
}
