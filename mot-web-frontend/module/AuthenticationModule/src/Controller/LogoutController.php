<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Controller;

use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use Zend\Mvc\Controller\AbstractActionController;

/**
 * Logout Controller is used to logout user sessions.
 */
class LogoutController extends AbstractActionController
{
    const ROUTE_LOGOUT = 'logout';

    /**
     * @var WebLogoutService
     */
    private $logoutService;

    /**
     * @param WebLogoutService $logoutService
     */
    public function __construct(WebLogoutService $logoutService)
    {
        $this->logoutService = $logoutService;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $this->logoutService->logout();

        return $this->redirect()->toRoute('login');
    }
}
