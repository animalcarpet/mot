<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeNameValidator extends AbstractValidator implements ValidatorInterface
{
    public function validate(array $data)
    {
        if (empty($data[AuthorisedExaminerPatchModel::NAME])) {
            $errorSchema = new ErrorSchema();
            $errorSchema->add('Name - must not be empty');
            $errorSchema->throwIfAny();
        }
    }
}
