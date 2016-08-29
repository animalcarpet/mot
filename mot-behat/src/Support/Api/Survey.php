<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;
use PHPUnit_Framework_Assert as PHPUnit;

class Survey extends MotApi
{
    const PATH_SURVEY_SHOULD_DISPLAY = 'survey/should-display';
    const PATH_SURVEY_CREATE = 'survey';

    /**
     * @param string $token
     * @param array  $motTestDetails
     *
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
     * @param string $authToken
     * @param array  $surveyValue
     * @param string $surveyToken
     */
    public function createSurvey($authToken, $surveyValue, $surveyToken)
    {
        $body = json_encode(
            [
                'satisfaction_survey' => $surveyValue,
                'token' => $surveyToken,
            ]
        );

        $result = $this->client->request(
            new Request(
                'POST',
                self::PATH_SURVEY_CREATE,
                ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $authToken],
                $body
            )
        );

        PHPUnit::assertEquals(200, $result->getStatusCode());
    }
}
