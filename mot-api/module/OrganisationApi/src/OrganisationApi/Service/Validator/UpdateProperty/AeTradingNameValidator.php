<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeTradingNameValidator extends AbstractValidator implements ValidatorInterface
{
    public function validate(array $data)
    {
        if (empty($data[AuthorisedExaminerPatchModel::TRADING_NAME])) {
            $errorSchema = new ErrorSchema();
            $errorSchema->add('Trading name - must not be empty');
            $errorSchema->throwIfAny();
        }
    }
}
