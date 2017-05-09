<?php

namespace PersonApi\Service\Validator;

use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class BasePersonValidator.
 */
class BasePersonValidator extends AbstractValidator
{
    const ERROR_EMAIL_INCORRECT_FORMAT = 'Incorrect email address format';

    private $requiredFields
        = [
            'firstName',
            'surname',
        ];

    /**
     * @param $data
     *
     * @throws RequiredFieldException
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validate($data)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty($this->requiredFields, $data);

        $this->errors->throwIfAny();
    }
}
