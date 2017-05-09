<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeAreaOfficeValidator extends AbstractValidator implements ValidatorInterface
{
    public function validate(array $data)
    {
        if (empty($data[AuthorisedExaminerPatchModel::AREA_OFFICE])) {
            $errorSchema = new ErrorSchema();
            $errorSchema->add('Area Office - must not be empty');
            $errorSchema->throwIfAny();
        }
    }
}
