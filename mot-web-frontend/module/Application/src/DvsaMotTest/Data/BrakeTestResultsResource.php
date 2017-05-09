<?php

namespace DvsaMotTest\Data;

use Application\Data\ApiResources;
use DvsaCommon\UrlBuilder\UrlBuilder;

class BrakeTestResultsResource extends ApiResources
{
    public function save($motTestNumber, $data)
    {
        $apiUrl = UrlBuilder::create()
            ->motTest()
            ->routeParam('motTestNumber', $motTestNumber)
            ->brakeTestResult();

        return $this->restSave($apiUrl, $data)['data'];
    }
}
