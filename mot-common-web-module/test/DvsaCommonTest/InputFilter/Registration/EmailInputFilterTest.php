<?php

namespace DvsaCommonTest\InputFilter\Registration;

use DvsaCommon\Factory\InputFilter\Registration\EmailInputFilterFactory;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\Bootstrap;
use Zend\Validator\Identical;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;
use Zend\Validator\Regex;

class EmailInputFilterTest extends \PHPUnit_Framework_TestCase
{
    const VALID_EMAIL = 'test@test.com';

    /** @var EmailInputFilter */
    private $emailInputFilter;

    public function setUp()
    {
        $factory = new EmailInputFilterFactory();
        $this->emailInputFilter = $factory->createService(Bootstrap::getServiceManager());
    }

    public function testValidationPassesWithValidInput()
    {
        $data = $this->setUpDataInCorrectFormat(self::VALID_EMAIL, self::VALID_EMAIL);
        $this->emailInputFilter->setData($data);

        $this->assertTrue($this->emailInputFilter->isValid());
        $this->assertEmpty($this->emailInputFilter->getMessages());
    }

    public function testValidationFailsWhenEmailAndConfirmationNotSame()
    {
        $data = $this->setUpDataInCorrectFormat(self::VALID_EMAIL, 'otheremail@email.com');
        $this->emailInputFilter->setData($data);

        $expectedMessages = [
            EmailInputFilter::FIELD_EMAIL_CONFIRM => [
             Identical::NOT_SAME => EmailInputFilter::MSG_EMAIL_CONFIRM_DIFFER,
            ]
        ];

        $this->assertFalse($this->emailInputFilter->isValid());
        $this->assertSame($expectedMessages, $this->emailInputFilter->getMessages());
    }

    public function testValidationFailsWhenEmailEmpty()
    {
        $data = $this->setUpDataInCorrectFormat('', self::VALID_EMAIL);
        $this->emailInputFilter->setData($data);

        $expectedMessages = [
            EmailInputFilter::FIELD_EMAIL => [
                EmailAddressValidator::INVALID_FORMAT => EmailInputFilter::MSG_EMAIL_INVALID,
                NotEmpty::IS_EMPTY => EmailInputFilter::MSG_EMAIL_INVALID,
            ],
            EmailInputFilter::FIELD_EMAIL_CONFIRM => [
                Identical::NOT_SAME => EmailInputFilter::MSG_EMAIL_CONFIRM_DIFFER,
            ],
        ];

        $this->assertFalse($this->emailInputFilter->isValid());
        $this->assertSame($expectedMessages, $this->emailInputFilter->getMessages());
    }

    public function testValidationFailsWhenEmailConfirmationEmpty()
    {
        $data = $this->setUpDataInCorrectFormat(self::VALID_EMAIL, '');
        $this->emailInputFilter->setData($data);

        $expectedMessages = [
            EmailInputFilter::FIELD_EMAIL_CONFIRM => [
                Identical::NOT_SAME => EmailInputFilter::MSG_EMAIL_CONFIRM_DIFFER,
                NotEmpty::IS_EMPTY => EmailInputFilter::MSG_EMAIL_CONFIRM_EMPTY,
            ]
        ];

        $this->assertFalse($this->emailInputFilter->isValid());
        $this->assertSame($expectedMessages, $this->emailInputFilter->getMessages());
    }

    public function testValidationFailsWhenBothFieldsEmpty()
    {
        $data = $this->setUpDataInCorrectFormat('', '');
        $this->emailInputFilter->setData($data);

        $expectedMessages = [
            EmailInputFilter::FIELD_EMAIL => [
                EmailAddressValidator::INVALID_FORMAT => EmailInputFilter::MSG_EMAIL_INVALID,
                NotEmpty::IS_EMPTY => EmailInputFilter::MSG_EMAIL_INVALID,
            ],
            EmailInputFilter::FIELD_EMAIL_CONFIRM => [
                NotEmpty::IS_EMPTY => EmailInputFilter::MSG_EMAIL_CONFIRM_EMPTY,
            ],
        ];

        $this->assertFalse($this->emailInputFilter->isValid());
        $this->assertSame($expectedMessages, $this->emailInputFilter->getMessages());
    }

    public function testValidationFailsWhenEmailOverMaxLength()
    {
        $email = str_repeat('a', 191) . '@email.com';
        $data = $this->setUpDataInCorrectFormat($email, $email);

        $this->emailInputFilter->setData($data);

        $expectedMessages = [
            EmailInputFilter::FIELD_EMAIL => [
                EmailAddressValidator::LENGTH_EXCEEDED => EmailInputFilter::MSG_EMAIL_INVALID,
            ],
        ];

        $this->assertFalse($this->emailInputFilter->isValid());
        $this->assertSame($expectedMessages, $this->emailInputFilter->getMessages());
    }


    private function setUpDataInCorrectFormat($email, $emailConfirmation)
    {
        return [
            EmailInputFilter::FIELD_EMAIL => $email,
            EmailInputFilter::FIELD_EMAIL_CONFIRM => $emailConfirmation,
        ];
    }
}
