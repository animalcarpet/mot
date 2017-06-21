<?php

namespace DvsaMotTest\Service;

use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

class BrakeTestConfigurationService
{
    public function __construct(Client $restClient)
    {
        $this->restClient = $restClient;
    }

    public function validateConfiguration($dto, $motTestNumber)
    {
        $apiUrl = UrlBuilder::of()->motTest()->routeParam('motTestNumber', $motTestNumber)
            ->brakeTestResult()->validateConfiguration();
        $this->restClient->post($apiUrl, DtoHydrator::dtoToJson($dto));
    }
}
