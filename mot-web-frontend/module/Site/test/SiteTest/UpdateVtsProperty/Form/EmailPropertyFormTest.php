<?php

namespace SiteTest\UpdateVtsProperty\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use Site\UpdateVtsProperty\Process\Form\EmailPropertyForm;
use Zend\Validator\StringLength;

class EmailPropertyFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validData
     */
    public function testFormForValidData(array $data)
    {
        $form = new EmailPropertyForm();
        $form->setData($data);

        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function validData()
    {
        return [
            [[EmailPropertyForm::FIELD_EMAIL => '']],
            [[EmailPropertyForm::FIELD_EMAIL => 'emailpropertyformtest@dvsa.test']],
        ];
    }

    /**
     * @dataProvider invalidData
     */
    public function testFormReturnsErrorMsgForInvalidData(array $data, $expectedMsg)
    {
        $form = new EmailPropertyForm();
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
                [EmailPropertyForm::FIELD_EMAIL => 'email'],
                [EmailAddressValidator::INVALID_FORMAT => EmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [EmailPropertyForm::FIELD_EMAIL => 'email@'],
                [EmailAddressValidator::INVALID_FORMAT => EmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [EmailPropertyForm::FIELD_EMAIL => 'email@email'],
                [EmailAddressValidator::INVALID_HOSTNAME => EmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [EmailPropertyForm::FIELD_EMAIL => 'email.com'],
                [EmailAddressValidator::INVALID_FORMAT => EmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG],
            ],
            [
                [EmailPropertyForm::FIELD_EMAIL => $this->createTooLongEmail()],
                [
                    EmailAddressValidator::LENGTH_EXCEEDED => EmailPropertyForm::EMAIL_ADDRESS_INVALID_MSG,
                    StringLength::TOO_LONG => str_replace('%max%', EmailPropertyForm::FIELD_EMAIL_MAX_LENGTH, EmailPropertyForm::EMAIL_ADDRESS_TOO_LONG_MSG),
                ],
            ],
        ];
    }

    private function createTooLongEmail()
    {
        $email = '';
        $length = EmailPropertyForm::FIELD_EMAIL_MAX_LENGTH;
        while ($length) {
            $email .= 'x';
            --$length;
        }

        return $email.'@'.EmailAddressValidator::TEST_DOMAIN;
    }
}
