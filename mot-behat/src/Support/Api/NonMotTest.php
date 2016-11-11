<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use DvsaCommon\Enum\MotTestTypeCode;

class NonMotTest extends AbstractMotTest
{
    const PATH = 'non-mot-test';

    public function getPath()
    {
        return self::PATH;
    }

    public function startMotTest($token = null, $vehicleId = '3', $testClass = '4')
    {
        $params = [
            'vehicleId' => $vehicleId,
            'vehicleTestingStationId' => '1',
            'primaryColour' => 'L',
            'secondaryColour' => 'L',
            'fuelTypeId' => 'PE',
            'vehicleClassCode' => $testClass,
            'hasRegistration' => '1',
            'oneTimePassword' => Authentication::ONE_TIME_PASSWORD,
            'motTestType' => MotTestTypeCode::NON_MOT_TEST
        ];

        return parent::createMotWithParams($token, $params);
    }
}
