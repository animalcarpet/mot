<?php
namespace Dvsa\Mot\Behat\Support\Data\Params;

use Dvsa\Mot\Behat\Support\Data\SiteData;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Validator\EmailAddressValidator;

class SiteParams
{
    const ID = "id";
    const SITE_NUMBER = "siteNumber";
    const NAME = "name";
    const SITE_NAME = "siteName";
    const STATUS = "status";
    const ADDRESS_LINE_1 = "addressLine1";
    const TOWN = "town";
    const SITE_TOWN = "siteTown";
    const SITE_POSTCODE = "sitePostcode";
    const POSTCODE = "postcode";
    const EMAIL = "email";
    const PHONE_NUMBER = "phoneNumber";
    const CLASSES = "classes";
    const TYPE = "type";
    const START_DATE = "startDate";
    const END_DATE = "endDate";

    public static function getDefaultParams()
    {
        return [
            self::NAME => SiteData::DEFAULT_NAME,
            self::STATUS => SiteStatusCode::APPROVED,
            self::ADDRESS_LINE_1 => "Baker Street",
            self::TOWN => "London",
            self::POSTCODE => "BT2 4RR",
            self::EMAIL => 'vtsbehatsupport@' . EmailAddressValidator::TEST_DOMAIN,
            self::PHONE_NUMBER => "01117 26374",
            self::CLASSES => VehicleClassCode::getAll(),
            self::TYPE => SiteTypeCode::VEHICLE_TESTING_STATION,
        ];
    }
}
