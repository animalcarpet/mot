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
        $body = json_encode($inputData);

        return $this->client->request(new Request(
            MotApi::METHOD_PUT,
            str_replace('{user_id}', $userId, self::PATH),
            ['Content-Type' => 'application/json'],
            $body
        ));
    }
}