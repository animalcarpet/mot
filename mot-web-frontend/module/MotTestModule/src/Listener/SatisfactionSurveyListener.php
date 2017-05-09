<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Listener;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use Dvsa\Mot\Frontend\AuthenticationModule\Event\SuccessfulSignOutEvent;
use Dvsa\Mot\Frontend\MotTestModule\Service\SurveyService;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use OutOfBoundsException;
use Zend\EventManager\Event;
use Zend\EventManager\EventManager;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Router\RouteStackInterface as Router;

/**
 * Listener for GDS Satisfaction Survey related events.
 */
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
     * @var Logger
     */
    private $logger;

    /**
     * @param SurveyService $surveyService
     * @param EventManager  $eventManager
     * @param Router        $router
     */
    public function __construct(SurveyService $surveyService, EventManager $eventManager, Router $router, Logger $logger)
    {
        $this->surveyService = $surveyService;
        $this->eventManager = $eventManager;
        $this->router = $router;
        $this->logger = $logger;
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
        /** @var MotTest $motDetails */
        $motDetails = $event->getParam('motDetails');

        $motTestId = $motDetails->getId();
        $motTestTypeCode = $motDetails->getTestTypeCode();
        $testerId = $motDetails->getTester()->getId();

        try {
            if ($this->surveyService->surveyShouldDisplay($motTestId, $motTestTypeCode, $testerId)) {
                $motTestNumber = $event->getParam('motTestNumber');
                $this->surveyService->generateToken($motTestNumber);
            }
        } catch (GeneralRestException $e) {
            $this->logger->err(sprintf('[GDS Satisfaction Survey] %s', $e->getMessage()));
        } catch (OutOfBoundsException $e) {
            $this->logger->err(sprintf('[GDS Satisfaction Survey] %s', $e->getMessage()));
        }
    }

    /**
     * @param Event $event
     *
     * @return Response
     */
    public function displaySurveyOnSignOut(Event $event)
    {
        $token = $event->getParam('token');
        $response = $event->getParam('response');
        $response->setStatusCode(303);

        if (!$token) {
            $response->getHeaders()->addHeaders(['Location' => $this->generateUrlFromRoute('login')]);

            return $response;
        }

        $response->getHeaders()->addHeaders(['Location' => $this->generateUrlFromRoute('survey', ['token' => $token])]);

        return $response;
    }

    /**
     * Generates a URL based on a route.
     *
     * @param string     $route   RouteInterface name
     * @param array      $params  Parameters to use in url generation, if any
     * @param array|bool $options RouteInterface-specific options to use in url generation, if any
     *
     * @return string
     */
    private function generateUrlFromRoute($route, $params = [], $options = [])
    {
        $options['name'] = $route;

        return $this->router->assemble($params, $options);
    }
}
