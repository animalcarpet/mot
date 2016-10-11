<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use Core\Service\SessionService;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\Survey\DownloadableSurveyReports;
use DvsaCommon\HttpRestJson\Client as HttpClient;
use OutOfBoundsException;
use Zend\EventManager\EventManagerInterface;

/**
 * Class SurveyService.
 */
class SurveyService
{
    const SURVEY_ENDPOINT = 'survey';
    const SHOULD_DISPLAY_ENDPOINT = 'survey/should-display';
    const HAS_BEEN_PRESENTED_ENDPOINT = 'survey/has-been-presented';
    const MARK_PRESENTED_ENDPOINT = 'survey/mark-presented';
    const REPORT_ENDPOINT = 'survey/reports';
    const TOKEN_VALIDATION_ENDPOINT = 'survey/token/validate';
    const TOKEN_API_ENDPOINT = 'survey/token';
    const MOT_SURVEY_TOKEN = 'mot_survey_token';

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var SessionService
     */
    private $sessionService;

    /**
     * @var EventManagerInterface
     */
    private $eventManager;

    /**
     * SurveyService constructor.
     *
     * @param HttpClient     $httpClient
     * @param SessionService $sessionService
     */
    public function __construct(HttpClient $httpClient, SessionService $sessionService)
    {
        $this->httpClient = $httpClient;
        $this->sessionService = $sessionService;
    }

    /**
     * @param int    $motTestId
     * @param string $motTestTypeCode
     * @param int    $testerId
     *
     * @return bool
     */
    public function surveyShouldDisplay($motTestId, $motTestTypeCode, $testerId)
    {
        $data = [
            'motTestId' => $motTestId,
            'motTestTypeCode' => $motTestTypeCode,
            'testerId' => $testerId,
        ];

        $apiEndpoint = self::SHOULD_DISPLAY_ENDPOINT;
        $result = $this->httpClient->post($apiEndpoint, $data);
        if (!isset($result['data'])) {
            throw new OutOfBoundsException(sprintf('"data" key is missing from API response for endpoint "%s"',
                $apiEndpoint));
        }

        return (bool) $result['data'];
    }

    /**
     * @param string $token
     *
     * @return bool
     */
    public function hasBeenPresented($token)
    {
        $response = $this->httpClient->post(self::HAS_BEEN_PRESENTED_ENDPOINT, ['token' => $token]);

        return isset($response['data']) ? $response['data'] : true;
    }

    /**
     * @param string $token
     *
     * @return mixed|string
     */
    public function markSurveyAsPresented($token)
    {
        return $this->httpClient->post(self::MARK_PRESENTED_ENDPOINT, ['token' => $token]);
    }

    /**
     * @param $surveyData
     *
     * @return mixed|string
     */
    public function submitSurveyResult($surveyData)
    {
        $result = $this->httpClient->post(self::SURVEY_ENDPOINT, $surveyData);

        return $result;
    }

    /**
     * @return DownloadableSurveyReports
     */
    public function getSurveyReports()
    {
        $result = $this->httpClient->get(self::REPORT_ENDPOINT);

        return DownloadableSurveyReports::fromApi($result);
    }

    /**
     * @param string $token
     *
     * @return string
     */
    public function isTokenValid($token)
    {
        $result = $this->httpClient->post(self::TOKEN_VALIDATION_ENDPOINT, ['token' => $token]);

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
        $data = ['motTestNumber' => $motTestNumber];

        // store token in db
        $result = $this->httpClient->post(self::TOKEN_API_ENDPOINT, $data);

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
