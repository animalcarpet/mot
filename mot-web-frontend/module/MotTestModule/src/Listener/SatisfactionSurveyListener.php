<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Listener;

use Dvsa\Mot\Frontend\AuthenticationModule\Event\SuccessfulSignOutEvent;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaMotTest\Service\SurveyService;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Router\RouteStackInterface as Router;

class SatisfactionSurveyListener
{
    /**
     * @var SurveyService
     */
    private $surveyService;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var Router
     */
    private $router;

    /**
     * @param SurveyService $surveyService
     * @param EventManager  $eventManager
     * @param Router        $router
     */
    public function __construct(SurveyService $surveyService, EventManager $eventManager, Router $router)
    {
        $this->surveyService = $surveyService;
        $this->eventManager = $eventManager;
        $this->router = $router;
    }

    public function attach()
    {
        $this->eventManager->attach(MotEvents::MOT_TEST_COMPLETED, [$this, 'generateSurveyTokenIfEligible']);
        $this->eventManager->attach(SuccessfulSignOutEvent::NAME, [$this, 'displaySurveyOnSignOut']);
    }

    /**
     * @param Event $event
     */
    public function generateSurveyTokenIfEligible(Event $event)
    {
        /** @var MotTestDto $motDetails */
        $motDetails = $event->getParam('motDetails');

        $motTestTypeCode = $motDetails->getTestType()->getCode();
        $testerId = $motDetails->getTester()->getId();

        if ($this->surveyService->surveyShouldDisplay($motTestTypeCode, $testerId)) {
            $motTestNumber = $event->getParam('motTestNumber');
            $this->surveyService->generateToken($motTestNumber);
        }
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function displaySurveyOnSignOut(Event $event)
    {
        $token  = $event->getParam('token');
        $response = $event->getParam('response');
        $response->setStatusCode(303);

        // token is empty array when not present in url
        if (is_null($token) || (is_array($token) && empty($token))) {
            $response->getHeaders()->addHeaders(
                [
                    'Location' => $this->generateUrlFromRoute('login'),
                    'Content-Type' => 'application/json',
                ]
            );

            return $response;
        }

        $response->getHeaders()->addHeaders(
            [
                'Location' => $this->generateUrlFromRoute('survey', ['token' => $token]),
                'Content-Type' => 'application/json',
            ]
        );

        return $response;
    }

    /**
     * Generates a URL based on a route.
     *
     * @param string     $route   RouteInterface name
     * @param array      $params  Parameters to use in url generation, if any
     * @param array|bool $options RouteInterface-specific options to use in url generation, if any.
     *
     * @return string
     */
    private function generateUrlFromRoute($route, $params = [], $options = [])
    {
        $options['name'] = $route;

        return $this->router->assemble($params, $options);
    }
}
