<?php

namespace DvsaCommonApi\Service\Validator;

use DvsaCommonApi\Service\Exception\RequiredFieldException;

/**
 * Class ContactDetailsValidator.
 */
class ContactDetailsValidator extends AbstractValidator implements ValidatorInterface
{
    const ERROR_EMAIL_CONFIRMATION = 'Email address and Confirm email address must match';
    const ERROR_EMAIL_INCORRECT_FORMAT = 'Incorrect email address format';

    /** @var AddressValidator */
    private $addressValidator;

    private $requiredFields
        = [
            'email',
            'phoneNumber',
        ];

    public function __construct(AddressValidator $addressValidator)
    {
        parent::__construct();
        $this->addressValidator = $addressValidator;
    }

    public function validate(array $data)
    {
        $this->validateAddress($data);

        try {
            $this->checkRequiredFields($this->requiredFields, $data);
            $this->validateEmail($data);
        } catch (RequiredFieldException $e) {
            $this->errors->addException($e);
        }

        $this->errors->throwIfAny();
    }

    private function validateAddress($data)
    {
        try {
            $this->addressValidator->validate($data);
        } catch (RequiredFieldException $e) {
            $this->errors->addException($e);
        }
    }

    private function validateEmail($data)
    {
        if (array_key_exists('emailConfirmation', $data)) {
            if (trim(mb_strtoupper($data['email'])) !== trim(mb_strtoupper($data['emailConfirmation']))) {
                $this->errors->add(self::ERROR_EMAIL_CONFIRMATION, 'emailConfirmation');
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors->add(self::ERROR_EMAIL_INCORRECT_FORMAT, 'email');
        }
    }
}
