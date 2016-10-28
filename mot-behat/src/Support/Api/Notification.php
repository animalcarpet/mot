<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\HttpClient;

class Notification extends MotApi
{
    const PATH = '/notification/person/';

    /**
     * @param string $token
     * @param int $personId
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function fetchNotificationForPerson($token, $personId)
    {
        return $this->sendRequest(
            $token,
            MotApi::METHOD_GET,
            self::PATH.$personId
        );
    }
}
