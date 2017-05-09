<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeStatusValidator extends AbstractValidator implements ValidatorInterface
{
    public function validate(array $data)
    {
        $errorSchema = new ErrorSchema();

        $status = $data[AuthorisedExaminerPatchModel::STATUS];

        if (empty($status)) {
            $errorSchema->add('Status - must not be empty');
        }

        $statuses = AuthorisationForAuthorisedExaminerStatusCode::getAll();

        if (!in_array($status, $statuses)) {
            $errorSchema->add('Status - status code is invalid');
        }

        $errorSchema->throwIfAny();
    }
}
