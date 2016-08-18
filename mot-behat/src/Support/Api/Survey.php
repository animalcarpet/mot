<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;
use PHPUnit_Framework_Assert as PHPUnit;

class Survey extends MotApi
{
    const PATH_SURVEY_SHOULD_DISPLAY = 'survey/shouldDisplay';
    const PATH_SURVEY_CREATE = 'survey';

    /**
     * @param string    $token
     * @param \StdClass $motTestDetails
     * @return bool
     */
    public function surveyIsDisplayed($token, $motTestDetails)
    {
        $body = json_encode(['motTestDetails' => $motTestDetails, 'surveyEnabled' => true]);

        $result = $this->client->request(
            new Request(
                'PUT',
                self::PATH_SURVEY_SHOULD_DISPLAY,
                ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $token],
                $body
            )
        );

        return $result->getBody()['data'];
    }

    /**
     * @param string $token
     * @param array    $surveyValue
     */
    public function createSurvey($token, $surveyValue)
    {
        $body = json_encode($surveyValue);
        
        $result = $this->client->request(
            new Request(
                'POST',
                self::PATH_SURVEY_CREATE,
                ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $token],
                $body
            )
        );

        PHPUnit::assertEquals(200, $result->getStatusCode());
    }
}
