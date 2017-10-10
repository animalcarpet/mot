<?php
namespace AccountTest\Action\LockedAccount;

use Account\Action\LockedAccount\ShowLockoutWarningAction;
use Account\ViewModel\Builder\LockedAccountViewModelBuilder;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Dashboard\Service\PasswordService;
use Dashboard\ViewModel\LockoutWarningViewModel;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use DvsaCommon\Configuration\MotConfig;
use DvsaCommonTest\TestUtils\XMock;
use Zend\View\Helper\Url;

class ShowLockoutWarningActionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ShowLockoutWarningAction */
    private $sut;

    /** @var PasswordService|\PHPUnit_Framework_MockObject_MockObject */
    private $passwordService;

    /** @var  Url|\PHPUnit_Framework_MockObject_MockObject */
    private $url;

    protected function setUp()
    {
        $this->url = XMock::of(Url::class);
        $this->passwordService = XMock::of(PasswordService::class);

        $this->sut = new ShowLockoutWarningAction(
            $this->createLockedAccountViewModelBuilder(),
            $this->url,
            $this->passwordService
        );
    }

    public function test_execute_redirectToHomePageWhenUserFailedNoPasswordAttempts()
    {
        $this->passwordService->method('isAccountLocked')->willReturn(false);
        $this->passwordService->method('shouldWarnUserAboutFailedAttempts')->willReturn(false);

        /** @var RedirectToRoute $result */
        $result = $this->sut->execute('placeholder/backlink');

        $this->assertInstanceOf(RedirectToRoute::class, $result);
        $this->assertEquals(ContextProvider::USER_HOME_ROUTE, $result->getRouteName());
    }

    public function test_execute_returnsLockoutWarningWithProperMessage()
    {
        $this->passwordService->method('isAccountLocked')->willReturn(false);
        $this->passwordService->method('shouldWarnUserAboutFailedAttempts')->willReturn(true);
        $this->url->method('__invoke')->willReturn('some/url');

        /** @var ViewActionResult $result */
        $result = $this->sut->execute(ChangeSecurityQuestionsAction::BACK_LINK);
        /** @var LockoutWarningViewModel $vm */
        $vm = $result->getViewModel();

        $this->assertInstanceOf(ViewActionResult::class, $result);
        $this->assertInstanceOf(LockoutWarningViewModel::class, $result->getViewModel());
        $this->assertEquals(ShowLockoutWarningAction::MESSAGE_HEADING, $vm->getHeading());
        $this->assertEquals(ShowLockoutWarningAction::MESSAGE, $vm->getMessage());
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