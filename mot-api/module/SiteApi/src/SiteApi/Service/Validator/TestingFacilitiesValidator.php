<?php

namespace SiteApi\Service\Validator;

use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommonApi\Service\Validator\AbstractValidator;

class TestingFacilitiesValidator extends AbstractValidator
{
    const FIELD_TESTING_FACILITY_OPTL = 'facilityOptl';
    const FIELD_TESTING_FACILITY_TPTL = 'facilityTptl';

    const ERR_TESTING_FACILITY_OPTL_REQUIRE = 'A number of OPTL must be selected';
    const ERR_TESTING_FACILITY_TPTL_REQUIRE = 'A number of TPTL must be selected';
    const ERR_TESTING_FACILITY_REQUIRE = 'A number for either OPTL or TPTL must be selected';

    /**
     * Validate testing facilities on site DTO.
     *
     * @param VehicleTestingStationDto $siteDto
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validate(VehicleTestingStationDto $siteDto)
    {
        if ($siteDto->isOptlSelected() === false) {
            $this->errors->add(self::ERR_TESTING_FACILITY_OPTL_REQUIRE, self::FIELD_TESTING_FACILITY_OPTL);
        }
        if ($siteDto->isTptlSelected() === false) {
            $this->errors->add(self::ERR_TESTING_FACILITY_TPTL_REQUIRE, self::FIELD_TESTING_FACILITY_TPTL);
        }
        if ($siteDto->isOptlSelected() === true && $siteDto->isTptlSelected() === true
            && empty($siteDto->getFacilities())) {
            $this->errors->add(self::ERR_TESTING_FACILITY_REQUIRE, self::FIELD_TESTING_FACILITY_OPTL);
        }

        $this->errors->throwIfAnyField();
    }
}
