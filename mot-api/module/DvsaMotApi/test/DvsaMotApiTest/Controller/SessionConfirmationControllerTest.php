<?php

namespace DvsaMotApiTest\Controller;

use DvsaAuthentication\Login\LoginService;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Controller\SessionConfirmationController;
use Zend\Http\Response;

class SessionConfirmationControllerTest extends AbstractMotApiControllerTestCase
{
    private $loginService;

    protected function setUp()
    {
        $this->loginService = XMock::of(LoginService::class);
        $this->controller = new SessionConfirmationController(new Response(), $this->loginService);

        parent::setUp();
    }

    public function testCreateResponseWithValidPasswordResponds200()
    {
        $this->loginService
            ->expects($this->any())
            ->method('confirmPassword')
            ->willReturn(true);

        $this->controller->create(['password' => 'mypassword']);

        $this->assertEquals(Response::STATUS_CODE_200, $this->controller->getResponse()->getStatusCode());
    }

    public function testCreateResponseWithInvalidPasswordResponds400()
    {
        $this->loginService
            ->expects($this->any())
            ->method('confirmPassword')
            ->willReturn(false);

        $this->controller->create(['password' => 'mypassword']);

        $this->assertEquals(Response::STATUS_CODE_422, $this->controller->getResponse()->getStatusCode());
    }

    public function testCreateResponseHandlesEmptySubmission()
    {
        $this->loginService
            ->expects($this->any())
            ->method('confirmPassword')
            ->willReturn(false);

        $this->controller->create([]);

        $this->assertEquals(Response::STATUS_CODE_422, $this->controller->getResponse()->getStatusCode());
    }
}
