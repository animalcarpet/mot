<?php

namespace Account\Controller;

use Account\Action\LockedAccount\SetCookieAndLogoutAction;
use Account\Action\LockedAccount\ShowLockoutWarningAction;
use Account\Action\LockedAccount\ShowMessageAction;
use Core\Controller\AbstractDvsaActionController;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class LockedAccountController extends AbstractDvsaActionController implements AutoWireableInterface
{
    const LOCKOUT_WARNING_ROUTE = "account/lockout-warning";
    const LOCKED_ACCOUNT_ROUTE = "account/locked";
    const SET_COOKIE_AND_LOGOUT = "account/locked/set-cookie";

    private $showMessageAction;
    private $cookieAndLogoutAction;
    private $showLockoutWarningAction;

    public function __construct(ShowMessageAction $showMessageAction, ShowLockoutWarningAction $showLockoutWarningAction,
                                SetCookieAndLogoutAction $cookieAndLogoutAction)
    {
        $this->showMessageAction = $showMessageAction;
        $this->showLockoutWarningAction = $showLockoutWarningAction;
        $this->cookieAndLogoutAction = $cookieAndLogoutAction;
    }

    public function setCookieAndLogoutAction()
    {
        return $this->applyActionResult($this->cookieAndLogoutAction->execute());
    }

    public function showMessageAction()
    {
        return $this->applyActionResult($this->showMessageAction->execute());
    }

    public function showLockoutWarningAction()
    {
        $backTo = $this->params()->fromQuery('backTo');
        return $this->applyActionResult($this->showLockoutWarningAction->execute($backTo));
    }
}