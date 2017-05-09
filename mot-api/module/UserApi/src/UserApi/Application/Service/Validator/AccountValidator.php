<?php

namespace UserApi\Application\Service\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\DrivingLicenceValidator;
use PersonApi\Service\Validator\BasePersonValidator;

/**
 * Class AccountValidator.
 */
class AccountValidator extends BasePersonValidator
{
    const ERROR_EMAIL_CONFIRMATION = 'Email address and Confirm email address must match';
    const ERROR_PASSWORD_CONFIRMATION = 'Password and Confirm Password must match';
    const ERROR_PASSWORD_TOO_SHORT = 'Password must be at least 8 characters long';
    const ERROR_PASSWORD_LOWER_LETTERS = 'Password must contain lowercase characters';
    const ERROR_PASSWORD_UPPER_LETTERS = 'Password must contain uppercase characters';
    const ERROR_PASSWORD_DIGIT = 'Password must contain digits';
    const ERROR_AGE_UNDER_16 = 'You must be at least 16 years of age';
    const PASSWORD_MIN_LENGTH = 8;
    const MINIMUM_AGE = 16;

    private $password;

    private $requiredFields
        = [
            'title',
            'dateOfBirth',
            'gender',
            'addressLine1',
            'town',
            'postcode',
            'phoneNumber',
            'email',
            'emailConfirmation',
            'password',
            'passwordConfirmation',
        ];

    public function validate($data)
    {
        try {
            parent::validate($data);
        } catch (RequiredFieldException $e) {
            $this->errors->addException($e);
        }

        try {
            RequiredFieldException::CheckIfRequiredFieldsNotEmpty($this->requiredFields, $data);
        } catch (RequiredFieldException $e) {
            $this->errors->addException($e);
        }

        $this->errors->throwIfAny();

        $this->validateEmail($data);
        $this->validatePassword($data);
        $this->validateDrivingLicenceAndRegion($data);
        try {
            $this->validateBirthDate($data['dateOfBirth']);
        } catch (DateException $e) {
            $this->errors->add($e->getMessage());
        }

        $this->errors->throwIfAny();
    }

    protected function validateEmail($data)
    {
        if (trim(mb_strtoupper($data['email'])) !== trim(mb_strtoupper($data['emailConfirmation']))) {
            $this->errors->add(self::ERROR_EMAIL_CONFIRMATION, 'emailConfirmation');
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors->add(self::ERROR_EMAIL_INCORRECT_FORMAT, 'email');
        }
    }

    private function validatePassword($data)
    {
        $this->password = $data['password'];

        if ($this->password !== $data['passwordConfirmation']) {
            $this->errors->add(self::ERROR_PASSWORD_CONFIRMATION, 'passwordConfirmation');
        }

        if (strlen($this->password) < self::PASSWORD_MIN_LENGTH) {
            $this->errors->add(self::ERROR_PASSWORD_TOO_SHORT, 'password');
        }

        $this->matchOrAddError('/[A-Z]/', self::ERROR_PASSWORD_UPPER_LETTERS);
        $this->matchOrAddError('/[a-z]/', self::ERROR_PASSWORD_LOWER_LETTERS);
        $this->matchOrAddError('/[0-9]/', self::ERROR_PASSWORD_DIGIT);
    }

    private function validateDrivingLicenceAndRegion($data)
    {
        if (!empty($data['drivingLicenceNumber']) && !empty($data['drivingLicenceRegion'])) {
            $drivingLicenceValidator = new DrivingLicenceValidator($this->errors);
            $drivingLicenceValidator->validateWithoutThrowing($data);
        }
    }

    private function matchOrAddError($match, $message)
    {
        if (!preg_match($match, $this->password)) {
            $this->errors->add($message, 'password');
        }
    }

    private function validateBirthDate($date)
    {
        if (!$this->isAgeOver16($date)) {
            $this->errors->add(self::ERROR_AGE_UNDER_16, 'yearOfBirth');
        }
    }

    private function isAgeOver16($date)
    {
        $birthDate = DateUtils::toDate($date);
        $today = DateUtils::today();
        $sixteenYearsAgo = clone $today;
        $sixteenYearsAgo->sub(new \DateInterval('P16Y'))->add(new \DateInterval('P1D'));

        return false === DateUtils::isDateInFuture($birthDate)
            && (false === DateUtils::isDateTimeBetween($birthDate, $sixteenYearsAgo, $today));
    }
}
