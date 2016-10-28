<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\HttpClient;

class DataCatalog extends MotApi
{
    const PATH = '/catalog';

    public function getData($token)
    {

        return $this->sendRequest($token, self::METHOD_GET, self::PATH);
    }
}
