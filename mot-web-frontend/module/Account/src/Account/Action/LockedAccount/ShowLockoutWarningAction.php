<?php

namespace Account\Action\LockedAccount;

use Account\ViewModel\Builder\LockedAccountViewModelBuilder;
use Core\Action\ActionResultInterface;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use Dashboard\Controller\PasswordController;
use Dashboard\Service\PasswordService;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Action\ChangeSecurityQuestionsAction;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Controller\ChangeSecurityQuestionsController;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\View\Helper\Url;

class ShowLockoutWarningAction implements AutoWireableInterface
{
    const MESSAGE_HEADING = 'You have entered an incorrect password';
    const MESSAGE = 'If you enter the wrong password 1 more time your account will lock for 30 minutes';

    private $viewModelBuilder;
    private $url;
    private $passwordService;

    public function __construct(
        LockedAccountViewModelBuilder $viewModelBuilder,
        Url $url,
        PasswordService $passwordService
    )
    {
        $this->viewModelBuilder = $viewModelBuilder;
        $this->url = $url;
        $this->passwordService = $passwordService;
    }

    public function execute(string $backTo): ActionResultInterface
    {
        $actionResult = new ViewActionResult();
        $this
            ->viewModelBuilder
            ->setHeading(self::MESSAGE_HEADING)
            ->setMessage(self::MESSAGE)
            ->setBackLinkText("Back");

        if (!$this->passwordService->shouldWarnUserAboutFailedAttempts() &&
            !$this->passwordService->isAccountLocked())
        {
            return new RedirectToRoute(ContextProvider::USER_HOME_ROUTE);
        }

        switch ($backTo) {
            case ChangeSecurityQuestionsAction::BACK_LINK:
                $this->viewModelBuilder->setBackLink($this->url->__invoke(ChangeSecurityQuestionsController::ROUTE));
                $actionResult->layout()->setPageTitle(ChangeSecurityQuestionsAction::CHANGE_SECURITY_QUESTIONS_START_PAGE_TITLE);
                $actionResult->layout()->setPageSubTitle(ChangeSecurityQuestionsAction::CHANGE_SECURITY_QUESTIONS_START_PAGE_SUBTITLE);
                $actionResult->layout()->setBreadcrumbs([
                    'Your profile' => $this->url->__invoke(ContextProvider::YOUR_PROFILE_PARENT_ROUTE),
                    'Change security questions' => ''
                ]);
                break;
            case PasswordController::BACK_LINK:
                $this->viewModelBuilder->setBackLink($this->url->__invoke(ContextProvider::YOUR_PROFILE_PARENT_ROUTE . '/change-password'));
                $actionResult->layout()->setPageTitle(PasswordController::PAGE_TITLE);
                $actionResult->layout()->setPageSubTitle(PasswordController::PAGE_SUBTITLE);
                $actionResult->layout()->setBreadcrumbs([
                    'Your profile' => $this->url->__invoke(ContextProvider::YOUR_PROFILE_PARENT_ROUTE),
                    'Change your password' => ''
                ]);
                break;
        }

        $vm = $this->viewModelBuilder->build();

        $actionResult->setViewModel($vm);

        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->setTemplate('dashboard/password/lockout-warning');

        return $actionResult;
    }
}
