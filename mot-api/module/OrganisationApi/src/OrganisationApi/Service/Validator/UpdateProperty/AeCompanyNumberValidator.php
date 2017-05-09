<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeCompanyNumberValidator extends AbstractValidator implements ValidatorInterface
{
    public function validate(array $data)
    {
        if (empty($data[AuthorisedExaminerPatchModel::COMPANY_NUMBER])) {
            $errorSchema = new ErrorSchema();
            $errorSchema->add('Company number - must not be empty');
            $errorSchema->throwIfAny();
        }
    }
}
