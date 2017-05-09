<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Enum\VehicleClassGroupCode;
use DvsaCommon\UrlBuilder\UrlBuilder;

class DemoTestAssessmentMapper extends Mapper
{
    public function createAssessment($testerId, $vehicleTestingGroup)
    {
        if ($vehicleTestingGroup != VehicleClassGroupCode::BIKES
            && $vehicleTestingGroup != VehicleClassGroupCode::CARS_ETC
        ) {
            throw new \InvalidArgumentException('Unknown VehicleClassGroupCode: '.$vehicleTestingGroup);
        }

        $url = (new UrlBuilder())->demoTestAssessment($testerId)->toString();

        $data = [
            'testerId' => $testerId,
            'vehicleClassGroup' => $vehicleTestingGroup,
        ];

        $this->client->post($url, $data);
    }
}
