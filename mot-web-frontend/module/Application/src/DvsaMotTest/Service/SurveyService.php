<?php

namespace DvsaMotTest\Service;

use Core\Service\SessionService;
use DvsaCommon\HttpRestJson\Client;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class SurveyService implements EventManagerAwareInterface
{
    const SURVEY_API_ENDPOINT = 'survey/should-display';
    const TOKEN_API_ENDPOINT = 'survey/token';
    const TOKEN_VALIDATION_API_ENDPOINT = 'survey/token/validate';
    const MOT_SURVEY_TOKEN = 'mot_survey_token';

    /** @var Client $client */
    private $client;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * @var SessionService
     */
    private $sessionService;

    /**
     * @param Client         $restClient
     * @param SessionService $sessionService
     */
    public function __construct(Client $restClient, SessionService $sessionService)
    {
        $this->client = $restClient;
        $this->sessionService = $sessionService;
    }

    /**
     * @param $motTestTypeCode
     * @param $testerId
     *
     * @return bool
     */
    public function surveyShouldDisplay($motTestTypeCode, $testerId)
    {
        $data = [
            'motTestTypeCode' => $motTestTypeCode,
            'testerId' => $testerId,
        ];

        $result = $this->client->post(self::SURVEY_API_ENDPOINT, $data);

        return $result['data'];
    }

    /**
     * Generate a unique token to be used for authenticating a tester's
     * satisfaction survey after they've logged out of the application.
     *
     * @param int $motTestNumber
     */
    public function generateToken($motTestNumber)
    {
        $data = [
            'motTestNumber' => $motTestNumber,
        ];

        // store token in db
        $result = $this->client->post(self::TOKEN_API_ENDPOINT, $data);

        // store token in session
        if (null !== $result['data'] && [] !== $result['data']) {
            $this->sessionService->save('mot_survey_token', $result['data']);
        }
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * @param EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $this->eventManager = $eventManager;
    }
}
