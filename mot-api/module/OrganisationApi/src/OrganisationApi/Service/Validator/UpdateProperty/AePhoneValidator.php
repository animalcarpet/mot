<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Utility\StringUtils;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AePhoneValidator extends AbstractValidator implements ValidatorInterface
{
    const MAX_PHONE_LENGTH = 24;

    private $phoneField = 'phone';

    public function __construct($prefix = '')
    {
        $this->appendPrefix($prefix);
    }

    private function appendPrefix($prefix)
    {
        if (!$prefix) {
            return;
        }

        $this->phoneField = $prefix.ucfirst($this->phoneField);
    }

    public function validate(array $data)
    {
        $this->checkRequiredFields([$this->phoneField], $data);

        $errorSchema = new ErrorSchema();

        if (array_key_exists($this->phoneField, $data)
            && StringUtils::strlen($data[$this->phoneField]) > self::MAX_PHONE_LENGTH
        ) {
            $errorSchema->add($this->phoneField.' - must be '.self::MAX_PHONE_LENGTH.' characters or less', $this->phoneField);
        }

        $errorSchema->throwIfAny();
    }
}
