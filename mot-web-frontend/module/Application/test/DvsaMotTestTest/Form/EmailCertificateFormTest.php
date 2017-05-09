<?php

namespace DvsaMotTest\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use DvsaMotTest\Form\EmailCertificateForm as Form;
use Zend\Validator\NotEmpty;
use Zend\Validator\Identical;
use Zend\Validator\StringLength;
use PHPUnit_Framework_TestCase;

class EmailCertificateFormTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var EmailCertificateForm
     */
    private $form;

    public function setUp()
    {
        $this->form = new EmailCertificateForm();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testIsValid($data, $errorMessages)
    {
        $this->form->setData($data);
        $isValid = $this->form->isValid();

        if (empty($errorMessages)) {
            $this->assertTrue($isValid);
        } else {
            $this->assertFalse($isValid);
        }

        foreach ($errorMessages as $fieldName => $errors) {
            $formMessages = $this->form->getMessages($fieldName);
            $this->assertEquals(count($errors), count($formMessages));

            foreach ($errors as $type => $message) {
                $this->assertEquals($message, $formMessages[$type]);
            }
        }
    }

    public function dataProvider()
    {
        return [
            //check if inputs are not empty
            [
                [
                    Form::FIELD_FIRST_NAME => '',
                    Form::FIELD_FAMILY_NAME => '',
                    Form::FIELD_EMAIL => '',
                    Form::FIELD_RETYPE_EMAIL => '',
                ],
                [
                    Form::FIELD_FIRST_NAME => [NotEmpty::IS_EMPTY => Form::MSG_FIRST_NAME_IS_EMPTY],
                    Form::FIELD_FAMILY_NAME => [NotEmpty::IS_EMPTY => Form::MSG_FAMILY_NAME_IS_EMPTY],
                    Form::FIELD_EMAIL => [
                        EmailAddressValidator::INVALID_FORMAT => Form::MSG_EMAIL_IS_INVALID,
                    ],
                ],
            ],

            //check if inputs are not too long
            [
                [
                    Form::FIELD_FIRST_NAME => 'abcdefghijabcdefghijabcdefghijabcdefghijabcdefghij',
                    Form::FIELD_FAMILY_NAME => 'abcdefghijabcdefghijabcdefghijabcdefghijabcdefghij',
                    Form::FIELD_EMAIL => 'abcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefghijabcdefgh@email',
                    Form::FIELD_RETYPE_EMAIL => '',
                ],
                [
                    Form::FIELD_FIRST_NAME => [StringLength::TOO_LONG => sprintf(Form::MSG_FIRST_NAME_TOO_LONG, Form::FIRST_NAME_MAX_LENGTH)],
                    Form::FIELD_FAMILY_NAME => [StringLength::TOO_LONG => sprintf(Form::MSG_FAMILY_NAME_TOO_LONG, Form::FAMILY_NAME_MAX_LENGTH)],
                    Form::FIELD_EMAIL => [
                        StringLength::TOO_LONG => sprintf(Form::MSG_EMAIL_TOO_LONG, Form::EMAIL_MAX_LENGTH),
                        EmailAddressValidator::INVALID_FORMAT => Form::MSG_EMAIL_IS_INVALID,
                    ],
                ],
            ],

            //check if emials are identical
            [
                [
                    Form::FIELD_FIRST_NAME => 'John',
                    Form::FIELD_FAMILY_NAME => 'Rambo',
                    Form::FIELD_EMAIL => 'emailcertificateformtest1@dvsa.test',
                    Form::FIELD_RETYPE_EMAIL => 'emailcertificateformtest@dvsa.test',
                ],
                [
                    Form::FIELD_RETYPE_EMAIL => [
                        Identical::NOT_SAME => Form::MSG_EMAIL_IS_NOT_IDENTICAL,
                    ],
                ],
            ],

            //valid data
            [
                [
                    Form::FIELD_FIRST_NAME => 'John',
                    Form::FIELD_FAMILY_NAME => 'Rambo',
                    Form::FIELD_EMAIL => 'emailcertificateformtest@dvsa.test',
                    Form::FIELD_RETYPE_EMAIL => 'emailcertificateformtest@dvsa.test',
                ],
                [],
            ],
        ];
    }
}
