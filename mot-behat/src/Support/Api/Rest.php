<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Request;

class Rest extends MotApi
{
    public function makeRequest($token, $method, $url)
    {
        return $this->sendRequest(
            $token,
            strtoupper($method),
            $url
        );
    }
}
