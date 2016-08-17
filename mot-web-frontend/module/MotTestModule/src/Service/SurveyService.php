<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use DvsaCommon\Dto\AbstractDataTransferObject;
use DvsaCommon\HttpRestJson\Client;

/**
 * Class SurveyService.
 */
class SurveyService
{
    const API_URL = 'survey';
    const REPORT_ENDPOINT = '/reports';
    const TOKEN_VALIDATION_ENDPOINT = '/token/validate';

    /**
     * @var Client
     */
    private $restClient;

    /**
     * SurveyService constructor.
     *
     * @param Client $restClient
     */
    public function __construct(
        Client $restClient
    ) {
        $this->restClient = $restClient;
    }

    /**
     * @param $surveyData
     *
     * @return mixed|string
     */
    public function submitSurveyResult($surveyData)
    {
        $result = $this->restClient->post(self::API_URL, $surveyData);

        return $result;
    }

    /**
     * @return AbstractDataTransferObject
     */
    public function getSurveyReports()
    {
        $result = $this->restClient->get(self::API_URL.self::REPORT_ENDPOINT);

        return $result;
    }

    /**
     * @param string $token
     * @return string
     */
    public function isTokenValid($token)
    {
        $result = $this->restClient->post(
            self::API_URL.self::TOKEN_VALIDATION_ENDPOINT,
            ['token' => $token]
        );

        return $result['data'];
    }
}
