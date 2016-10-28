<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;
use PHPUnit_Framework_Assert as PHPUnit;

class Survey extends MotApi
{
    const PATH_SURVEY_SHOULD_DISPLAY = 'survey/should-display';

    /**
     * @param string $token
     * @param array  $motTestDetails
     *
     * @return bool
     */
    public function surveyIsDisplayed($token, $motTestDetails)
    {
        $result = $this->sendPutRequest(
            $token,
            self::PATH_SURVEY_SHOULD_DISPLAY,
            ['motTestDetails' => $motTestDetails, 'surveyEnabled' => true]
        );

        return $result->getBody()->getData();
    }
}
