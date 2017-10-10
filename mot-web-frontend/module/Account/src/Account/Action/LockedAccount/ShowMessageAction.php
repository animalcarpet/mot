<?php

namespace Account\Action\LockedAccount;

use Account\Service\LockedAccountCookieService;
use Account\ViewModel\Builder\LockedAccountViewModelBuilder;
use Core\Action\ActionResultInterface;
use Core\Action\RedirectToRoute;
use Core\Action\ViewActionResult;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\View\Helper\Url;

class ShowMessageAction implements AutoWireableInterface
{
    const MESSAGE_HEADING = "Your account is locked";
    const MESSAGE = "Try to sign in again in %d minutes or contact the helpdesk";

    private $lockedAccountCookieService;
    private $viewModelBuilder;
    private $url;

    public function __construct(
        LockedAccountCookieService $lockedAccountCookieService,
        LockedAccountViewModelBuilder $viewModelBuilder,
        Url $url
    )
    {
        $this->lockedAccountCookieService = $lockedAccountCookieService;
        $this->viewModelBuilder = $viewModelBuilder;
        $this->url = $url;
    }

    public function execute(): ActionResultInterface
    {
        $timeLeft = $this->lockedAccountCookieService->getTimeLeftInMin();

        if ($timeLeft <= 0) {
            return new RedirectToRoute("login");
        }

        $this
            ->viewModelBuilder
            ->setHeading(self::MESSAGE_HEADING)
            ->setMessage(sprintf(self::MESSAGE, $timeLeft))
            ->setBackLinkText("Return to sign in")
            ->setBackLink($this->url->__invoke("login"));

        $vm = $this->viewModelBuilder->build();

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($vm);

        $actionResult->layout()->setPageTitle("MOT testing service");
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->setTemplate('dashboard/password/lockout-warning');
        $actionResult->layout()->setShowOrganisationLogo(true);

        return $actionResult;
    }
}
