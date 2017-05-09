<?php

namespace SiteApi\Service\Validator;

use DvsaCommon\Dto\Site\SiteContactDto;
use DvsaCommon\Dto\Site\VehicleTestingStationDto;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use OrganisationApi\Service\Validator\ContactValidator;

class SiteValidator extends AbstractValidator
{
    /** @var ContactValidator */
    private $contactValidator;
    /** @var TestingFacilitiesValidator */
    protected $testingFacilitiesValidator;
    /** @var SiteDetailsValidator */
    private $siteDetailsValidator;

    public function __construct(
        $errors,
        TestingFacilitiesValidator $testingFacilitiesValidator,
        SiteDetailsValidator $siteDetailsValidator
    ) {
        parent::__construct($errors);
        $this->contactValidator = new ContactValidator();
        $this->testingFacilitiesValidator = $testingFacilitiesValidator;
        $this->siteDetailsValidator = $siteDetailsValidator;
    }

    public function validate(VehicleTestingStationDto $siteDto)
    {
        $this->validateContactDetails($siteDto);
        $this->validateSiteDetail($siteDto);
        $this->validateFacilities($siteDto);

        $this->errors->throwIfAnyField();
    }

    public function validateSiteDetail(VehicleTestingStationDto $siteDto)
    {
        $this->siteDetailsValidator->validate($siteDto);
    }

    public function validateSiteDetailOnEdit(VehicleTestingStationDto $siteDto)
    {
        $this->siteDetailsValidator->validate($siteDto, false);
    }

    public function validateFacilities(VehicleTestingStationDto $siteDto)
    {
        $this->testingFacilitiesValidator->validate($siteDto);
    }

    /**
     * @param VehicleTestingStationDto $siteDto
     */
    private function validateContactDetails(VehicleTestingStationDto $siteDto)
    {
        /** @var SiteContactDto $contactDto */
        foreach ($siteDto->getContacts() as $contactDto) {
            $this->errors = $this->contactValidator->validate($contactDto);
        }
    }
}
