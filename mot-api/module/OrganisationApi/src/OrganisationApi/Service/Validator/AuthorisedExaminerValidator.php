<?php

namespace OrganisationApi\Service\Validator;

use DvsaCommon\Dto\Organisation\AuthorisedExaminerAuthorisationDto;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommonApi\Service\Validator\AbstractValidator;

class AuthorisedExaminerValidator extends AbstractValidator
{
    const FIELD_ORGANISATION_NAME = 'organisationName';
    const FIELD_COMPANY_TYPE = 'companyType';
    const FIELD_REG_NUMBER = 'registeredCompanyNumber';
    const FIELD_STATUS = 'status';
    const FIELD_AO_NUMBER = 'assignedAreaOffice';

    const REG_NUMBER_PATTERN = '/^([0-9]{8}|[a-zA-Z]{2}[0-9]{6})$/';

    const ERR_ORGANISATION_NAME_REQUIRE = 'You must enter a business name';
    const ERR_COMPANY_TYPE_REQUIRE = 'You must choose a business type';
    const ERR_COMPANY_NUMBER_REQUIRE = 'You must enter a company number';
    const ERR_COMPANY_NUMBER_WRONG_LENGTH = 'Must be 8 characters long';
    const ERR_COMPANY_NUMBER_NO_PATTERN_MATCH = 'Must be a valid company number, in the format AA123456 or 12345678';
    const ERR_STATUS = 'You must choose a status';
    const ERR_AO_NR_REQUIRE = 'You must choose an area office';

    /** @var ContactValidator */
    private $contactValidator;

    public function __construct($errors = null)
    {
        parent::__construct($errors);
        $this->contactValidator = new ContactValidator();
    }

    /**
     * @param OrganisationDto $organisationDto
     * @param array           $validAreaOffices
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validate(OrganisationDto $organisationDto, $validAreaOffices)
    {
        //  --  Validate contact   --
        /** @var OrganisationContactDto $contactDto */
        foreach ($organisationDto->getContacts() as $contactDto) {
            $this->errors = $this->contactValidator->validate($contactDto);
        }

        $this->validateAeDetail($organisationDto, $validAreaOffices);

        $this->errors->throwIfAnyField();
    }

    private function validateAeDetail(OrganisationDto $organisationDto, $validAreaOffices)
    {
        if ($this->isEmpty($organisationDto->getName())) {
            $this->errors->add(self::ERR_ORGANISATION_NAME_REQUIRE, self::FIELD_ORGANISATION_NAME);
        }

        if ($this->isEmpty($organisationDto->getCompanyType())
            || CompanyTypeCode::exists($organisationDto->getCompanyType()) === false) {
            $this->errors->add(self::ERR_COMPANY_TYPE_REQUIRE, self::FIELD_COMPANY_TYPE);
        }

        $authForAeDto = $organisationDto->getAuthorisedExaminerAuthorisation();
        if (is_null($authForAeDto)) {
            $this->errors->add(self::ERR_AO_NR_REQUIRE, self::FIELD_AO_NUMBER);
        } else {
            $this->validateAreaOffice($authForAeDto, $validAreaOffices);
        }
        $this->validateCompanyNumber($organisationDto);
    }

    private function validateAreaOfficeNumber($intAONumber, $validAreaOffices)
    {
        foreach ($validAreaOffices as $areaOffice) {
            if ((int) $intAONumber == (int) $areaOffice['areaOfficeNumber']) {
                return;
            }
        }
        $this->errors->add(self::ERR_AO_NR_REQUIRE, self::FIELD_AO_NUMBER);
    }

    private function validateCompanyNumber(OrganisationDto $organisationDto)
    {
        /*
         * If Business Type is "Company" then it needs to be verified not empty
         */
        if ($organisationDto->getCompanyType() === CompanyTypeCode::COMPANY
            && $this->isEmpty($organisationDto->getRegisteredCompanyNumber())) {
            $this->errors->add(self::ERR_COMPANY_NUMBER_REQUIRE, self::FIELD_REG_NUMBER);
        } elseif (!$this->isEmpty($organisationDto->getRegisteredCompanyNumber())
            && 8 !== strlen($organisationDto->getRegisteredCompanyNumber())) {
            $this->errors->add(self::ERR_COMPANY_NUMBER_WRONG_LENGTH, self::FIELD_REG_NUMBER);
        } elseif (!$this->isEmpty($organisationDto->getRegisteredCompanyNumber())
            && 0 === preg_match(self::REG_NUMBER_PATTERN, $organisationDto->getRegisteredCompanyNumber())) {
            $this->errors->add(self::ERR_COMPANY_NUMBER_NO_PATTERN_MATCH, self::FIELD_REG_NUMBER);
        }
    }

    public function validateAreaOffice(AuthorisedExaminerAuthorisationDto $dto, $validAreaOffices)
    {
        $intAONumber = $dto->getAssignedAreaOffice();

        if ($this->isEmpty($intAONumber)) {
            $this->errors->add(self::ERR_AO_NR_REQUIRE, self::FIELD_AO_NUMBER);
        } else {
            $this->validateAreaOfficeNumber($intAONumber, $validAreaOffices);
        }

        return $this;
    }

    public function failOnErrors()
    {
        $this->errors->throwIfAnyField();

        return $this;
    }
}
