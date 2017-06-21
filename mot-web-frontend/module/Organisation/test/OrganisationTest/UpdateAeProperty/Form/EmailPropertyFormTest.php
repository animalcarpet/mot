<?php

namespace Organisation\UpdateAeProperty\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use Organisation\UpdateAeProperty\Process\Form\RegisteredEmailPropertyForm;
use Zend\Validator\StringLength;

class EmailPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new RegisteredEmailPropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[RegisteredEmailPropertyForm::FIELD_EMAIL => '']],
            [[RegisteredEmailPropertyForm::FIELD_EMAIL => 'emailpropertyformtest@dvsa.test']],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data, $expectedMsg)
    {
        $form = new RegisteredEmailPropertyForm();
        $form->setData($data);

        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());

        $invalidEmailMsg = $form->getEmailElement()->getMessages();
        $this->assertCount(count($expectedMsg), $invalidEmailMsg);
        $this->assertEquals($expectedMsg, $invalidEmailMsg);
    }

    public function invalidData()
    {
        return [
            [
                [RegisteredEmailPropertyForm::FIELD_EMAIL => 'email'],
                [EmailAddressValidator::INVALID_FORMAT => RegisteredEmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [RegisteredEmailPropertyForm::FIELD_EMAIL => 'email@'],
                [EmailAddressValidator::INVALID_FORMAT => RegisteredEmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [RegisteredEmailPropertyForm::FIELD_EMAIL => 'email@email'],
                [EmailAddressValidator::INVALID_HOSTNAME => RegisteredEmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [RegisteredEmailPropertyForm::FIELD_EMAIL => 'email.com'],
                [EmailAddressValidator::INVALID_FORMAT => RegisteredEmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [RegisteredEmailPropertyForm::FIELD_EMAIL => $this->createTooLongEmail()],
                [
                    EmailAddressValidator::LENGTH_EXCEEDED => RegisteredEmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG,
                    StringLength::TOO_LONG => str_replace('%max%', RegisteredEmailPropertyForm::FIELD_EMAIL_MAX_LENGTH, RegisteredEmailPropertyForm::EMAIL_ADDRESS_TOO_LONG_MSG),
                ],
            ],
        ];
    }

    private function createTooLongEmail()
    {
        $email = '';
        $length = RegisteredEmailPropertyForm::FIELD_EMAIL_MAX_LENGTH;
        while ($length) {
            $email .= 'x';
            --$length;
        }

        return $email.'@'.EmailAddressValidator::TEST_DOMAIN;
    }
}
