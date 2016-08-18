<?php

namespace Dvsa\Mot\Frontend\MotTestModule\Service;

use DvsaCommon\HttpRestJson\Client;

/**
 * Class SurveyService.
 */
class SurveyService
{
    const API_URL = 'survey';
    const REPORT_ENDPOINT = '/reports';

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
     * @param $data
     *
     * @return mixed|string
     */
    public function submitSurveyResult($data)
    {
        $result = $this->restClient->post(self::API_URL, $data);

        return $result;
    }

    /**
     * @return array
     */
    public function getSurveyReports()
    {
        $result = $this->restClient->get(self::API_URL.self::REPORT_ENDPOINT);

        return $result;
    }
}
