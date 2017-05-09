<?php

namespace SiteApi\Service\Validator;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommon\Enum\CountryCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommonApi\Service\Validator\AbstractValidator;

class SiteDetailsValidator extends AbstractValidator
{
    const FIELD_LOCATION_TYPE = 'location';
    const FIELD_VEHICLE_CLASSES = 'classes';
    const FIELD_STATUS = 'status';
    const FIELD_NAME = 'name';
    const FIELD_COUNTRY = 'country';

    const ERR_LOCATION_TYPE_REQUIRE = 'A location type must be selected';
    const ERR_VEHICLE_CLASSES_MUST_BE_ARRAY = 'Vehicle classes must be an array';
    const ERR_VEHICLE_CLASS_MUST_BE_INTEGER = 'Vehicle class must be an integer';
    const ERR_STATUS_REQUIRE = 'Site status must be selected';
    const ERR_NAME_MUST_BE_NOT_EMPTY = 'Site name must be not empty';
    const ERR_WRONG_COUNTRY = 'This country is not allowed';

    /**
     * Validates basic site details.
     *
     * @param VehicleTestingStationDto $siteDto
     * @param bool                     $checkType
     * @param bool                     $checkStatus
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     *
     * @internal param bool $update
     */
    public function validate(VehicleTestingStationDto $siteDto, $checkType = true, $checkStatus = true)
    {
        if (true == $checkType) {
            $this->validateType($siteDto);
        }

        if (true == $checkStatus) {
            $this->validateStatus($siteDto);
        }

        $this->errors->throwIfAnyField();
    }

    public function validateType(VehicleTestingStationDto $siteDto)
    {
        if ($this->isEmpty(trim($siteDto->getType())) || SiteTypeCode::exists($siteDto->getType()) === false) {
            $this->errors->add(self::ERR_LOCATION_TYPE_REQUIRE, self::FIELD_LOCATION_TYPE);
        }
    }

    public function validateStatus(VehicleTestingStationDto $siteDto)
    {
        $status = $siteDto->getStatus();

        if (empty($status) || !in_array($status, $this->getAllowedStatuses())) {
            $this->errors->add(self::ERR_STATUS_REQUIRE, self::FIELD_STATUS);
        }
    }

    public function validateTestClasses(VehicleTestingStationDto $siteDto)
    {
        $classes = $siteDto->getTestClasses();

        if (!is_array($classes)) {
            $this->errors->add(self::ERR_VEHICLE_CLASSES_MUST_BE_ARRAY, self::FIELD_VEHICLE_CLASSES);
        } else {
            array_walk($classes, function ($item) {
                if (!is_int($item)) {
                    $this->errors->add(self::ERR_VEHICLE_CLASS_MUST_BE_INTEGER, self::FIELD_VEHICLE_CLASSES);
                }
            });
        }
    }

    public function validateName(VehicleTestingStationDto $siteDto)
    {
        if (!empty($siteDto->getName())) {
            return true;
        } else {
            $this->errors->add(self::ERR_NAME_MUST_BE_NOT_EMPTY, self::FIELD_NAME);
        }
    }

    public function validateCountry(VehicleTestingStationDto $siteDto)
    {
        //todo the same list is used in Form, etract both to common class
        if (in_array($siteDto->getCountry(), [
            CountryCode::WALES,
            CountryCode::SCOTLAND,
            CountryCode::ENGLAND,
        ])) {
            return true;
        } else {
            $this->errors->add(self::ERR_WRONG_COUNTRY, self::FIELD_COUNTRY);
        }
    }

    private function getAllowedStatuses()
    {
        return [
            'AV',
            'AP',
            'RE',
            'RJ',
            'LA',
            'EX',
        ];
    }
}
