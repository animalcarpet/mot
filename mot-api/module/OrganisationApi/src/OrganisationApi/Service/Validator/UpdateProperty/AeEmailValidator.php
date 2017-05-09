<?php

namespace OrganisationApi\Service\Validator\UpdateProperty;

use DvsaCommon\Utility\StringUtils;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Service\Validator\ValidatorInterface;

class AeEmailValidator extends AbstractValidator implements ValidatorInterface
{
    const MAX_EMAIL_LENGTH = 255;

    private $emailField = 'email';

    public function __construct($prefix = '')
    {
        $this->appendPrefix($prefix);
    }

    private function appendPrefix($prefix)
    {
        if (!$prefix) {
            return;
        }

        $this->emailField = $prefix.ucfirst($this->emailField);
    }

    public function validate(array $data)
    {
        if (!array_key_exists($this->emailField, $data)) {
            throw new RequiredFieldException($this->emailField);
        }

        $email = $data[$this->emailField];

        $email = trim($email);

        if ($email === '') {  // empty email is okay
            return;
        }

        $errorSchema = new ErrorSchema();

        $validator = new EmailAddressValidator();
        if (!$validator->isValid($data[$this->emailField])) {
            $errorSchema->add($this->emailField.' - is invalid email', $this->emailField);
        }

        if (StringUtils::strlen($data[$this->emailField]) > self::MAX_EMAIL_LENGTH
        ) {
            $errorSchema->add($this->emailField.' - must be '.self::MAX_EMAIL_LENGTH.' characters or less', $this->emailField);
        }

        $errorSchema->throwIfAny();
    }
}
