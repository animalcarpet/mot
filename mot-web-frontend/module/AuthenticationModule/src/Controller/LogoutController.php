<?php

/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
namespace Dvsa\Mot\Frontend\AuthenticationModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Service\SessionService;
use Dvsa\Mot\Frontend\AuthenticationModule\Event\SuccessfulSignOutEvent;
use Dvsa\Mot\Frontend\AuthenticationModule\Service\WebLogoutService;
use DvsaMotTest\Service\SurveyService;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\PhpEnvironment\Response;

/**
 * Logout Controller is used to logout user sessions.
 */
class LogoutController extends AbstractAuthActionController
{
    const ROUTE_LOGOUT = 'logout';

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var SessionService
     */
    private $sessionService;

    /**
     * @var WebLogoutService
     */
    private $logoutService;

    /**
     * @param EventManagerInterface $eventManager
     * @param SessionService        $sessionService
     * @param WebLogoutService      $logoutService
     */
    public function __construct(
        EventManagerInterface $eventManager,
        SessionService $sessionService,
        WebLogoutService $logoutService
    ) {
        $this->eventManager = $eventManager;
        $this->sessionService = $sessionService;
        $this->logoutService = $logoutService;
    }

    /**
     * @return \Zend\Http\Response
     */
    public function logoutAction()
    {
        $token = $this->sessionService->load(SurveyService::MOT_SURVEY_TOKEN);
        $response = $this->getServiceLocator()->get('Response');

        $this->logoutService->logout();

        $results = $this->eventManager->trigger(SuccessfulSignOutEvent::NAME, $this, ['token' => $token, 'response' => $response]);

        if (!empty($results)) {
            foreach ($results as $result) {
                if (get_class($result) === Response::class) {
                    return $results->first();
                }
            }
        }

        return $this->redirect()->toRoute('login');
    }
}
