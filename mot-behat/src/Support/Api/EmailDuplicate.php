<?php

namespace Dvsa\Mot\Behat\Support\Api;

class EmailDuplicate extends MotApi
{
    const PATH = "/person/email/is-duplicate?email=";

    /**
     * @param array $inputData
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function checkIsDuplicate($token, $email)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH . $email
        );
    }
}