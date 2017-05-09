<?php

namespace SiteApi\Service\Validator;

use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class NominateRoleValidator.
 */
class NominateRoleValidator extends AbstractValidator
{
    private $requiredFields
        = [
            'nomineeId',
            'roleCode',
        ];

    public function validate($data)
    {
        $this->checkRequiredFields($this->requiredFields, $data);

        $roleCode = $data['roleCode'];

        if (!SiteBusinessRoleCode::exists($roleCode)) {
            $this->errors->add('Site Role "'.$roleCode.'" does not exist', 'roleCode');
        }

        $this->errors->throwIfAny();
    }
}
