<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service;

use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\HttpRestJson\Client;

class PasswordValidationService
{
    const ROUTE = 'session/confirmation';

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function isPasswordValid($password)
    {
        try {
            $this->client->post(self::ROUTE, ['password' => $password]);

            return true;
        } catch (ValidationException $e) {
            return false;
        }
    }
}
