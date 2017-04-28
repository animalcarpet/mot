<?php
namespace Dvsa\Mot\Behat\Support\Data\Params;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Enum\ColourCode;
use DvsaCommon\Enum\CountryOfRegistrationId;

class ReplacementCertificateDraftUpdateParams
{
    const VTS_SITE_NUMBER = "vtsSiteNumber";
    const ODOMETER_READING = "odometerReading";
    const ODOMETER_VALUE = "value";
    const ODOMETER_UNIT = "unit";
    const COUNTRY_OF_REGISTRATION = "countryOfRegistration";
    const VEHICLE_REGISTRATION_MARK = "vrm";
    const VEHICLE_IDENTIFICATION_NUMBER = "vin";
    const PRIMARY_COLOUR = "primaryColour";
    const SECONDARY_COLOUR = "secondaryColour";

    public static function getDefaultParams()
    {
        return [
            "countryOfRegistration" => CountryOfRegistrationId::S_SE_SWEDEN,
            "primaryColour" => ColourCode::GREEN,
            "secondaryColour" => ColourCode::NOT_STATED,

            "odometerReading" => [
                "value" => 111222333,
                "unit" => OdometerUnit::KILOMETERS,
                "resultType" => OdometerReadingResultType::OK
            ],
            "vin" => "1M8GDM9AXKP042788",
            "vrm" => "FNZ6110"
        ];
    }
}
