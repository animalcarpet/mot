<?php

namespace Dvsa\Mot\Behat\Support\Api;

class AccountRegistration extends MotApi
{
    const PATH = "/account/register";

    /**
     * No token is required for the registration of the user
     * @param array $inputData
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function registerUser(array $inputData)
    {
        return $this->sendPostRequest(
            null,
            self::PATH,
            $inputData
        );
    }
}
