<?php

namespace Dvsa\Mot\Behat\Support\Api;

class SecurityQuestionsAnswer extends MotApi
{
    const PATH = "/person/{user_id}/security-questions/verify";

    /**
     * @param array $inputData
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function answerQuestions($userId, array $inputData)
    {
        return $this->sendPostRequest(
            null,
            str_replace('{user_id}', $userId, self::PATH),
            [
                'questionsAndAnswers' => $inputData
            ]
        );
    }
}
