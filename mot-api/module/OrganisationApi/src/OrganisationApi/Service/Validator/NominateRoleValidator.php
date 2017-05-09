<?php

namespace OrganisationApi\Service\Validator;

use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class NominateRoleValidator.
 */
class NominateRoleValidator extends AbstractValidator
{
    private $requiredFields
        = [
            'nomineeId',
            'roleId',
        ];

    public function validate($data)
    {
        $this->checkRequiredFields($this->requiredFields, $data);
    }
}
