<?php

namespace DvsaCommonApi\Service\Validator;

/**
 * Class AddressValidator.
 */
class AddressValidator extends AbstractValidator implements ValidatorInterface
{
    private $requiredFields = [
        'addressLine1',
        'town',
        'postcode',
    ];

    public function validate(array $data)
    {
        $this->checkRequiredFields($this->requiredFields, $data);
    }
}
