<?php
namespace AccountTest\Action\LockedAccount;

use Account\Action\LockedAccount\SetCookieAndLogoutAction;
use Account\Controller\LockedAccountController;
use Account\Service\LockedAccountCookieService;
use Core\Action\RedirectToRoute;
use Dashboard\Service\PasswordService;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\Headers;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;

class SetCookieAndLogoutActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var SetCookieAndLogoutAction */
    private $sut;

    /** @var Response|\PHPUnit_Framework_MockObject_MockObject */
    private $response;
    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    private $request;
    /** @var PasswordService|\PHPUnit_Framework_MockObject_MockObject */
    private $passwordService;

    protected function setUp()
    {
        $this->response = XMock::of(Response::class);
        $this->request = XMock::of(Request::class);

        $this->passwordService = XMock::of(PasswordService::class);

        $this->sut = new SetCookieAndLogoutAction(
            new LockedAccountCookieService($this->response, $this->request),
            XMock::of(WebLogoutService::class),
            $this->passwordService
        );
    }

    public function test_executeThrowsException_whenUserAccountIsNotLocked()
    {
        $this->passwordService->method("isAccountLocked")->willReturn(false);

        $this->expectException(\LogicException::class);
        $this->sut->execute();
    }

    public function test_executeReturnsCorrectResult_whenUserAccountLocked()
    {
        $this->passwordService->method("isAccountLocked")->willReturn(true);

        $headers = XMock::of(Headers::class);
        $headers->expects($this->once())->method("addHeader");

        $this
            ->response
            ->expects($this->once())
            ->method("getHeaders")
            ->willReturn($headers);

        $result = $this->sut->execute();

        $this->assertInstanceOf(RedirectToRoute::class, $result);

        $this->assertEquals(LockedAccountController::LOCKED_ACCOUNT_ROUTE, $result->getRouteName());
    }
}