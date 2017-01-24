<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultVehicleTestingStation;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class DemoTest extends AbstractMotTest
{
    const PATH = 'mot-demo-test';

    public function getPath()
    {
        return self::PATH;
    }

    public function startMotTest($token = null, $vehicleId, $testClass = VehicleClassCode::CLASS_4)
    {
        $params = [
            'vehicleId' => $vehicleId,
            'vehicleTestingStationId' => null,
            'primaryColour' => ColourCode::GREY,
            'secondaryColour' => ColourCode::GREY,
            'fuelTypeId' => FuelTypeCode::PETROL,
            'countryOfRegistration' => CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN,
            'vehicleClassCode' => $testClass,
            'hasRegistration' => '1',
            'cylinderCapacity' => 1700,
        ];

        return parent::createMotWithParams($token, $params);
    }
}
