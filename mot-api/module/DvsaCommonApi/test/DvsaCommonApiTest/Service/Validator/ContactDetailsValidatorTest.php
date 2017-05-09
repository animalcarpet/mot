<?php

namespace DvsaCommonApiTest\Service\Validator;

use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApi\Service\Validator\AddressValidator;
use DvsaCommonApi\Service\Validator\ContactDetailsValidator;

/**
 * I'm building my professional career on comments.
 */
class ContactDetailsValidatorTest extends AbstractServiceTestCase
{
    public function testValidatePassThrough()
    {
        $input = [
            'phoneNumber' => 'phoneNumber',
            'email' => 'contactdetailvalidatortest@'.EmailAddressValidator::TEST_DOMAIN,
            'emailConfirmation' => 'contactdetailvalidatortest@'.EmailAddressValidator::TEST_DOMAIN,
        ];

        $this->createContactDetailsValidator()->validate($input);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateMissingRequiredFieldsShouldThrowException()
    {
        $this->createContactDetailsValidator()->validate([]);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateDifferentEmailsShouldThrowsException()
    {
        $input = [
            'phoneNumber' => 'phoneNumber',
            'email' => 'contactdetailvalidatortest@'.EmailAddressValidator::TEST_DOMAIN,
            'emailConfirmation' => 'contactdetailvalidatortest1@'.EmailAddressValidator::TEST_DOMAIN,
        ];

        $this->createContactDetailsValidator()->validate($input);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testValidateInclorectEmailFormatShouldThrowsException()
    {
        $input = [
            'phoneNumber' => 'phoneNumber',
            'email' => 'aaaaa',
            'emailConfirmation' => 'aaaaa',
        ];

        $this->createContactDetailsValidator()->validate($input);
    }

    /**
     * @return ContactDetailsValidator
     */
    private function createContactDetailsValidator()
    {
        return new ContactDetailsValidator(
            $this->getMockWithDisabledConstructor(AddressValidator::class)
        );
    }
}
