<?php
namespace AccountTest\Action\LockedAccount;

use Account\Action\LockedAccount\ShowMessageAction;
use Account\Service\LockedAccountCookieService;
use Account\ViewModel\Builder\LockedAccountViewModelBuilder;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommon\Date\DateUtils;
use DvsaCommonTest\TestUtils\XMock;
use Zend\Http\PhpEnvironment\Request;
use Zend\Http\PhpEnvironment\Response;
use Zend\View\Helper\Url;

class ShowMessageActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ShowMessageAction */
    private $sut;

    /** @var Response|\PHPUnit_Framework_MockObject_MockObject */
    private $response;
    /** @var Request|\PHPUnit_Framework_MockObject_MockObject */
    private $request;

    protected function setUp()
    {
        $this->response = XMock::of(Response::class);
        $this->request = XMock::of(Request::class);

        $url = XMock::of(Url::class);
        $url->method("__invoke")->willReturn("login");

        $this->sut = new ShowMessageAction(
            $this->createLockedAccountCookieService(),
            $this->createLockedAccountViewModelBuilder(),
            $url
        );
    }

    public function test_execute_redirectToNewPage_whenCookieExpires()
    {
        $result = $this->sut->execute();
        $this->assertInstanceOf(RedirectToRoute::class, $result);
    }

    public function test_execute_returnsViewModel_ifUserAccountIsBlocked()
    {
        $cookieDateTime = DateUtils::nowAsUserDateTime();
        $cookieDateTime->add(new \DateInterval("PT" . 25 . "M"));

        $this
            ->request
            ->method("getCookie")
            ->willReturn([LockedAccountCookieService::COOKIE_NAME => $cookieDateTime->getTimestamp()])
        ;

        $result = $this->sut->execute();
        $this->assertInstanceOf(ViewActionResult::class, $result);
    }

    private function createLockedAccountCookieService(): LockedAccountCookieService
    {
        return new LockedAccountCookieService($this->response, $this->request);
    }

    private function createMotConfig(): MotConfig
    {
        $helpDesk = [];
        $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_PHONE_NUMBER] = "6356392783";
        $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_WEEKDAYS] = "08:00 - 16:00";
        $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SATURDAY] = "10:00 - 14:00";
        $helpDesk[LostOrForgottenCardController::VIEW_MODEL_PARAM_HELPDESK_OPENING_HOURS_SUNDAY] = "";

        $config = [LockedAccountViewModelBuilder::MOT_CONFIG => $helpDesk];

        return new MotConfig($config);
    }

    private function createLockedAccountViewModelBuilder(): LockedAccountViewModelBuilder
    {
        return new LockedAccountViewModelBuilder($this->createMotConfig());
    }
}