<?php

namespace Dvsa\Mot\Behat\Support\Api;

class SecurityQuestionsAnswer extends MotApi
{
    const PATH = "/person/{user_id}/security-questions/verify";

    /**
     * @param $token
     * @param array $inputData
     * @param $userId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function answerQuestions($token, array $inputData, $userId)
    {
        return $this->sendPostRequest(
            $token,
            str_replace('{user_id}', $userId, self::PATH),
            [
                'questionsAndAnswers' => $inputData
            ]
        );
    }
}
