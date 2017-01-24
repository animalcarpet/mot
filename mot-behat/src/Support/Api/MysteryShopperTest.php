<?php

namespace Dvsa\Mot\Behat\Support\Api;

use Dvsa\Mot\Behat\Datasource\Authentication;
use Dvsa\Mot\Behat\Support\HttpClient;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\VehicleClassCode;

class MysteryShopperTest extends AbstractMotTest
{
    const PATH = 'mot-test';

    /**
     * @var Person
     */
    private $person;

    public function __construct(HttpClient $client, Person $person)
    {
        parent::__construct($client);

        $this->person = $person;
    }

    public function getPath()
    {
        return self::PATH;
    }

    /**
     * @param string $token
     * @param string $vehicleId
     * @param string $siteId
     * @param string $testClass
     *
     * @param array $params
     * @return \Dvsa\Mot\Behat\Support\Response
     */
    public function startMOTTest($token, $vehicleId, $siteId, $testClass = VehicleClassCode::CLASS_4, $params = [])
    {
        $defaults = [
            'vehicleId' => $vehicleId,
            'vehicleTestingStationId' => $siteId,
            'primaryColour' => ColourCode::GREY,
            'secondaryColour' => ColourCode::GREY,
            'fuelTypeId' => FuelTypeCode::PETROL,
            'vehicleClassCode' => $testClass,
            'countryOfRegistration' => CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN,
            'hasRegistration' => '1',
            'cylinderCapacity' => 1700,
        ];

        $params = array_replace($defaults, $params);

        return parent::createMotWithParams($token, $params);
    }
}
