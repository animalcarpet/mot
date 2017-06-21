<?php

namespace Dvsa\Mot\Behat\Support\Api;

class SecurityQuestionsChange extends MotApi
{
    const PATH = "/security-question/{user_id}";

    /**
     * @param $token
     * @param array $inputData
     * @param $userId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function changeQuestions($token, array $inputData, $userId)
    {
        return $this->sendPutRequest(
            $token,
            str_replace('{user_id}', $userId, self::PATH),
            $inputData
        );
    }
}