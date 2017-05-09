<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Utility\StringUtils;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeAddressValidator extends AbstractValidator implements ValidatorInterface
{
    const MAX_ADDRESS_LINE_LENGTH = 50;
    const MAX_POSTCODE_LENGTH = 10;
    const MAX_TOWN_LENGTH = 50;
    const MAX_COUNTRY_LENGTH = 50;

    private $requiredFields = [];

    private $addressLine1Field = 'addressLine1';
    private $addressLine2Field = 'addressLine2';
    private $addressLine3Field = 'addressLine3';
    private $addressTownField = 'addressTown';
    private $addressCountryField = 'addressCountry';
    private $addressPostcodeField = 'addressPostcode';

    public function __construct($prefix = '')
    {
        $this->appendPrefix($prefix);

        $this->requiredFields = [
            $this->addressLine1Field,
            $this->addressTownField,
            $this->addressPostcodeField,
        ];
    }

    private function appendPrefix($prefix)
    {
        if (!$prefix) {
            return;
        }

        $this->addressLine1Field = $prefix.ucfirst($this->addressLine1Field);
        $this->addressLine2Field = $prefix.ucfirst($this->addressLine2Field);
        $this->addressLine3Field = $prefix.ucfirst($this->addressLine3Field);
        $this->addressTownField = $prefix.ucfirst($this->addressTownField);
        $this->addressCountryField = $prefix.ucfirst($this->addressCountryField);
        $this->addressPostcodeField = $prefix.ucfirst($this->addressPostcodeField);
    }

    public function validate(array $data)
    {
        $this->checkRequiredFields($this->requiredFields, $data);

        $errorSchema = new ErrorSchema();

        if (StringUtils::strlen($data[$this->addressLine1Field]) > self::MAX_ADDRESS_LINE_LENGTH) {
            $errorSchema->add($this->addressLine1Field.' - must be '.self::MAX_ADDRESS_LINE_LENGTH.' characters or less', $this->addressLine1Field);
        }

        if (array_key_exists($this->addressLine2Field, $data)
            && StringUtils::strlen($data[$this->addressLine2Field]) > self::MAX_ADDRESS_LINE_LENGTH
        ) {
            $errorSchema->add($this->addressLine2Field.' - must be '.self::MAX_ADDRESS_LINE_LENGTH.' characters or less', $this->addressLine2Field);
        }

        if (array_key_exists($this->addressLine3Field, $data)
            && StringUtils::strlen($data[$this->addressLine3Field]) > self::MAX_ADDRESS_LINE_LENGTH
        ) {
            $errorSchema->add($this->addressLine3Field.' - must be '.self::MAX_ADDRESS_LINE_LENGTH.' characters or less', $this->addressLine3Field);
        }

        if (StringUtils::strlen($data[$this->addressTownField]) > self::MAX_TOWN_LENGTH) {
            $errorSchema->add($this->addressTownField.' - must be '.self::MAX_TOWN_LENGTH.' characters or less', $this->addressTownField);
        }

        if (array_key_exists($this->addressCountryField, $data)
            && StringUtils::strlen($data[$this->addressCountryField]) > self::MAX_COUNTRY_LENGTH
        ) {
            $errorSchema->add($this->addressCountryField.' - must be '.self::MAX_COUNTRY_LENGTH.' characters or less', $this->addressCountryField);
        }

        if (StringUtils::strlen($data[$this->addressPostcodeField]) > self::MAX_POSTCODE_LENGTH) {
            $errorSchema->add($this->addressPostcodeField.' - must be '.self::MAX_POSTCODE_LENGTH.' characters or less', $this->addressPostcodeField);
        }

        $errorSchema->throwIfAny();
    }
}
