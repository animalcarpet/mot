<?php
namespace Dvsa\Mot\Behat\Support\HttpClient;

use DvsaCommon\HttpRestJson\ZendClient;

class ReflectiveClient extends ZendClient
{
    public function __construct($apiUrl)
    {
        if (substr($apiUrl, -1) !== "/") {
            $apiUrl .= "/";
        }

        parent::__construct(new \Zend\Http\Client(), $apiUrl);
    }
}
