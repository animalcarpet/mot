<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeTypeValidator extends AbstractValidator implements ValidatorInterface
{
    public function validate(array $data)
    {
        $errorSchema = new ErrorSchema();
        $typeCode = $data[AuthorisedExaminerPatchModel::TYPE];

        if (empty($typeCode)) {
            $errorSchema->add('Type - must not be empty');
        }

        $typeCodes = CompanyTypeCode::getAll();
        if (!in_array($typeCode, $typeCodes)) {
            $errorSchema->add('Type - type code is incorrect');
        }

        if ($typeCode == CompanyTypeCode::COMPANY) {
            if (!array_key_exists(AuthorisedExaminerPatchModel::COMPANY_NUMBER, $data)) {
                $errorSchema->add('Company number - must me provided if type is COMPANY');
            } else {
                $companyNumber = $data[AuthorisedExaminerPatchModel::COMPANY_NUMBER];
                if (empty($companyNumber)) {
                    $errorSchema->add('Company number - must not be empty if type is COMPANY');
                }
            }
        }

        $errorSchema->throwIfAny();
    }
}
