<?php
namespace Dvsa\Mot\Behat\Support\Data\Params;

use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultMake;
use Dvsa\Mot\Behat\Support\Data\DefaultData\DefaultModel;
use Dvsa\Mot\Behat\Support\Data\Map\MakeMap;
use Dvsa\Mot\Behat\Support\Data\Map\TransmissionTypeMap;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\ColourId;
use DvsaCommon\Enum\CountryOfRegistrationId;
use DvsaCommon\Enum\FuelTypeCode;
use DvsaCommon\Enum\FuelTypeId;
use DvsaCommon\Enum\VehicleClassCode;
use TestSupport\Helper\DataGeneratorHelper;

class VehicleParams
{
    const ID = "id";
    const COUNTRY_OF_REGISTRATION_ID = 'countryOfRegistrationId';
    const MAKE_ID = "makeId";
    const MAKE = "make";
    const MAKE_OTHER = "makeOther";
    const MAKE_NAME = "makeName";
    const MODEL = "model";
    const MODEL_OTHER = "modelOther";
    const MODEL_ID = "modelId";
    const MODEL_NAME = "modelName";
    const TRANSMISSION_ID = "transmissionTypeId";
    const COLOUR_CODE = "colourCode";
    const SECONDARY_COLOUR_CODE = "secondaryColourCode";
    const CYLINDER_CAPACITY = "cylinderCapacity";
    const FUEL_TYPE_ID = "fuelTypeId";
    const FUEL_TYPE_CODE = "fuelTypeCode";
    const TEST_CLASS = "testClass";
    const REGISTRATION = "registration";
    const REGISTRATION_NUMBER = "registrationNumber";
    const VIN = "vin";
    const DATE_OF_REGISTRATION = "dateOfRegistration";
    const DATE_OF_MANUFACTURE = "dateOfManufacture";
    const DATE_OF_FIRST_USE = "dateOfFirstUse";
    const ONE_TIME_PASSWORD = "oneTimePassword";
    const MANUFACTURE_DATE = "manufactureDate";
    const VEHICLE_ID = "vehicleId";
    const VEHICLE_CLASS = "vehicleClass";
    const WEIGHT = "weight";

    public static function getDefaultParams()
    {
        $dataGenerator = DataGeneratorHelper::buildForDifferentiator([]);

        return [
            self::COUNTRY_OF_REGISTRATION_ID => CountryOfRegistrationId::GB_UK_ENG_CYM_SCO_UK_GREAT_BRITAIN,
            self::MAKE_ID => DefaultMake::get()->getId() ,//BMW
            self::MODEL_ID => DefaultModel::get()->getId(), //Alpina
            self::TRANSMISSION_ID => (new TransmissionTypeMap())->getManualTypeId(),
            self::COLOUR_CODE => ColourCode::RED,
            self::SECONDARY_COLOUR_CODE => ColourCode::RED,
            self::CYLINDER_CAPACITY => 1300,
            self::FUEL_TYPE_CODE => FuelTypeCode::PETROL,
            self::TEST_CLASS => VehicleClassCode::CLASS_4,
            self::REGISTRATION_NUMBER => $dataGenerator->generateRandomString(7),
            self::VIN => $dataGenerator->generateRandomString(17),
            self::DATE_OF_REGISTRATION => '1980-01-01',
            self::DATE_OF_MANUFACTURE => '1980-01-01',
            self::DATE_OF_FIRST_USE => '1980-01-01',
            self::ONE_TIME_PASSWORD => '123456',
        ];
    }
}
