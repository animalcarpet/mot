<?php

namespace UserAdmin\Presenter;

use DvsaCommon\Enum\LicenceCountryCode;

class DrivingLicenceSummaryPresenter
{
    const DRIVING_LICENCE_REGION_GB = 'Great Britain (England, Scotland and Wales)';
    const DRIVING_LICENCE_REGION_NI = 'Northern Ireland';
    const DRIVING_LICENCE_REGION_NU = 'Non-United Kingdom';

    /**
     * @var array
     */
    private $countryDescriptions = [
        LicenceCountryCode::GREAT_BRITAIN_ENGLAND_SCOTLAND_AND_WALES => self::DRIVING_LICENCE_REGION_GB,
        LicenceCountryCode::NORTHERN_IRELAND => self::DRIVING_LICENCE_REGION_NI,
        LicenceCountryCode::NON_UNITED_KINGDOM => self::DRIVING_LICENCE_REGION_NU,
    ];

    private $countryCodes = [
        self::DRIVING_LICENCE_REGION_GB => LicenceCountryCode::GREAT_BRITAIN_ENGLAND_SCOTLAND_AND_WALES,
        self::DRIVING_LICENCE_REGION_NI => LicenceCountryCode::NORTHERN_IRELAND,
        self::DRIVING_LICENCE_REGION_NU => LicenceCountryCode::NON_UNITED_KINGDOM,
    ];

    /**
     * @var int
     */
    private $personId;

    /**
     * @param int $personId
     *
     * @return $this
     */
    public function setPersonId($personId)
    {
        $this->personId = $personId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return $this->personId;
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function getCountryDescriptionByCode($code)
    {
        if (isset($this->countryDescriptions[$code])) {
            return $this->countryDescriptions[$code];
        }
    }

    /**
     * @param string $description
     *
     * @return string
     */
    public function getCountryCodeByDescription($description)
    {
        if (isset($this->countryCodes[$description])) {
            return $this->countryCodes[$description];
        }
    }
}
