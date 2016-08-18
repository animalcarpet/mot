<?php

namespace DvsaMotTest\Service;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\Utility\DtoHydrator;

class SurveyService
{
    const SURVEY_API_ENDPOINT = 'survey/shouldDisplay';

    /** @var Client $client */
    private $client;

    public function __construct(Client $restClient)
    {
        $this->client = $restClient;
    }

    /**
     * @param MotTestDto $motTestDetails
     * @param bool       $surveyIsEnabled
     * @return bool
     */
    public function surveyShouldDisplay($motTestDetails)
    {
        $data = [
            'motTestDetails' => DtoHydrator::dtoToJson($motTestDetails)
        ];

        $result = $this->client->post(self::SURVEY_API_ENDPOINT, $data);

        return $result['data'];
    }
}
