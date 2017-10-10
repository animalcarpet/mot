<?php

namespace Account\Action\LockedAccount;

use Account\Controller\LockedAccountController;
use Account\Service\LockedAccountCookieService;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Dashboard\Service\PasswordService;

class SetCookieAndLogoutAction implements AutoWireableInterface
{
    private $lockedAccountCookieService;
    private $webLogoutService;
    private $passwordService;

    public function __construct(
        LockedAccountCookieService $lockedAccountCookieService,
        WebLogoutService $webLogoutService,
        PasswordService $passwordService
    )
    {
        $this->lockedAccountCookieService = $lockedAccountCookieService;
        $this->webLogoutService = $webLogoutService;
        $this->passwordService = $passwordService;
    }

    public function execute(): RedirectToRoute
    {
        $isAccountLocked = $this->passwordService->isAccountLocked();

        if ($isAccountLocked === false) {
            throw new \LogicException("User account is not locked");
        }

        $this->lockedAccountCookieService->setCookie();
        $this->webLogoutService->logout();

        return new RedirectToRoute(LockedAccountController::LOCKED_ACCOUNT_ROUTE);
    }
}
