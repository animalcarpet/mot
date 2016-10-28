<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class SecurityQuestionsChange extends MotApi
{
    const PATH = "/security-question/{user_id}";

    /**
     * @param array $inputData
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function changeQuestions($userId, array $inputData)
    {
        return $this->sendPutRequest(
            null,
            str_replace('{user_id}', $userId, self::PATH),
            $inputData
        );
    }
}