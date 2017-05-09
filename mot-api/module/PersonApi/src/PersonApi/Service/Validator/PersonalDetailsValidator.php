<?php

namespace PersonApi\Service\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use Zend\Validator\EmailAddress as EmailValidator;

/**
 * Class PersonalDetailsValidator.
 */
class PersonalDetailsValidator extends AbstractValidator
{
    const ERROR_INCORRECT_DATE = 'Invalid date of birth';
    const ERROR_DATE_IN_FUTURE = 'Date in future';
    const ERROR_EMAIL_CONFIRMATION_FAILED = 'Email confirmation does not match the email provided';
    const ERROR_EMAIL_ADDRESS_INVALID = 'Email address is not valid';

    /**
     * @var array
     */
    private $requiredFields = [
        'title',
        'firstName',
        'surname',
        'gender',
        'dateOfBirth',
    ];

    /**
     * @var array
     */
    private $requiredAddressFields = [
        'addressLine1',
        'town',
        'postcode',
    ];

    /**
     * @var array
     */
    private $addressFields = [
        'addressLine1',
        'addressLine2',
        'addressLine3',
        'town',
        'postcode',
    ];

    /**
     * @param $data
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function validate($data)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty($this->requiredFields, $data);
        $this->validateContactDetails($data);
        $this->validateDateOfBirth($data['dateOfBirth']);

        $this->errors->throwIfAny();
    }

    /**
     * @param array $data
     * @param bool  $throwExceptions
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     * @throws \DvsaCommonApi\Service\Exception\RequiredFieldException
     */
    public function validateContactDetails(array $data, $throwExceptions = false)
    {
        // Address is optional, but if one of the address fields is provided then we need to check all of them.
        $this->validateAddress($data);
        if ($data['email'] !== $data['emailConfirmation']) {
            $this->errors->add(self::ERROR_EMAIL_CONFIRMATION_FAILED, 'emailConfirmation');
        }
        if (!empty($data['email']) && !$this->validateEmail($data['email'])) {
            $this->errors->add(self::ERROR_EMAIL_ADDRESS_INVALID, 'email');
        }
        if ($throwExceptions) {
            $this->errors->throwIfAny();
        }
    }

    /**
     * @return array
     */
    public function getAddressFields()
    {
        return $this->addressFields;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function hasAddressData(array $data)
    {
        foreach ($this->addressFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @throws \DvsaCommonApi\Service\Exception\RequiredFieldException
     *
     * @return bool
     */
    public function validateAddress(array $data)
    {
        if (true === $this->hasAddressData($data)) {
            // The user provided address information so let's trigger address validation.
            $addressData = array_intersect_key($data, array_flip($this->requiredAddressFields));
            RequiredFieldException::CheckIfRequiredFieldsNotEmpty($this->requiredAddressFields, $addressData);
        }

        return true;
    }

    /**
     * @param $data
     *
     * @return bool
     */
    public function hasPhoneData($data)
    {
        return isset($data['phoneNumber']) && !empty($data['phoneNumber']);
    }

    /**
     * Validate email address is valid
     * (Domain validation disabled).
     *
     * @param string $email
     *
     * @return bool valid email address
     */
    public function validateEmail($email)
    {
        $validator = new EmailValidator();
        $validator->setOptions(['domain' => false]);

        return $validator->isValid($email);
    }

    /**
     * @param $dateOfFirstUse
     */
    private function validateDateOfBirth($dateOfFirstUse)
    {
        try {
            if (DateUtils::isDateInFuture(DateUtils::toDate($dateOfFirstUse))) {
                $this->errors->add(self::ERROR_DATE_IN_FUTURE, 'dateOfBirth');
            }
        } catch (\Exception $e) {
            $this->errors->add(self::ERROR_INCORRECT_DATE, 'dateOfBirth');
        }
    }
}
