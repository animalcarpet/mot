<?php

namespace OrganisationApi\Service\Validator;

use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Validator\AbstractValidator;

class ContactValidator extends AbstractValidator
{
    const FIELD_CONTACT = '%s[%s]';
    const FIELD_LINE1 = 'addressLine1';
    const FIELD_TOWN = 'addressTown';
    const FIELD_POSTCODE = 'addressPostCode';
    const FIELD_PHONE = 'phoneNumber';
    const FIELD_EMAIL = 'email';
    const FIELD_EMAIL_CONFIRM = 'emailConfirmation';

    const ERR_ADDRESS_REQUIRE = 'An address must be entered';
    const ERR_TOWN_REQUIRE = 'A town must be entered';
    const ERR_POSTCODE_REQUIRE = 'A postcode must be entered';
    const ERR_PHONE_REQUIRE = 'A telephone number must be entered';
    const ERR_EMAIL_INVALID = 'The email address you entered is not valid';
    const ERR_CONF_NOT_SAME = 'Both email addresses need to be the same';

    public function validate(ContactDto $contactDto)
    {
        $this->validateAddress($contactDto);
        $this->validatePhoneNumber($contactDto);
        $this->validateEmail($contactDto);

        return $this->errors;
    }

    public function validateAddress(ContactDto $contactDto)
    {
        if ($this->isEmpty(trim($contactDto->getAddress()->getAddressLine1()))) {
            $this->errors->add(
                self::ERR_ADDRESS_REQUIRE,
                sprintf(self::FIELD_CONTACT, $contactDto->getType(), self::FIELD_LINE1)
            );
        }
        if ($this->isEmpty(trim($contactDto->getAddress()->getTown()))) {
            $this->errors->add(
                self::ERR_TOWN_REQUIRE,
                sprintf(self::FIELD_CONTACT, $contactDto->getType(), self::FIELD_TOWN)
            );
        }
        if ($this->isEmpty(trim($contactDto->getAddress()->getPostcode()))) {
            $this->errors->add(
                self::ERR_POSTCODE_REQUIRE,
                sprintf(self::FIELD_CONTACT, $contactDto->getType(), self::FIELD_POSTCODE)
            );
        }
    }

    public function validatePhoneNumber(ContactDto $contactDto)
    {
        if ($this->isEmpty(trim($contactDto->getPrimaryPhoneNumber()))) {
            $this->errors->add(
                self::ERR_PHONE_REQUIRE,
                sprintf(self::FIELD_CONTACT, $contactDto->getType(), self::FIELD_PHONE)
            );
        }
    }

    public function validateEmail(ContactDto $contactDto)
    {
        if ($contactDto->getPrimaryEmail()->isSupplied() === true) {
            $validator = new EmailAddressValidator();

            if ($validator->isValid(trim($contactDto->getPrimaryEmail()->getEmail())) === false) {
                $this->errors->add(
                    self::ERR_EMAIL_INVALID,
                    sprintf(self::FIELD_CONTACT, $contactDto->getType(), self::FIELD_EMAIL)
                );
            }
            if (strtolower(trim($contactDto->getPrimaryEmail()->getEmail()))
                != strtolower(trim($contactDto->getPrimaryEmail()->getEmailConfirm()))) {
                $this->errors->add(
                    self::ERR_CONF_NOT_SAME,
                    sprintf(self::FIELD_CONTACT, $contactDto->getType(), self::FIELD_EMAIL_CONFIRM)
                );
            }
        }
    }
}
