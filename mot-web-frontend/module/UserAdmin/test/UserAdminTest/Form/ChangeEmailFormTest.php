<?php

namespace UserAdminTest\Form;

use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Form\ChangeEmailForm;

class ChangeEmailFormTest extends \PHPUnit_Framework_TestCase
{
    /** @var  EmailAddressValidator */
    private $emailValidator;

    public function setUp()
    {
        $this->emailValidator = XMock::of(EmailAddressValidator::class);
    }

    public function testIsValid_validData_shouldNotDisplayAnyErrors()
    {
        $form = $this->buildController();
        $form->setData($this->setDataValues('success@simulator.amazonses.com', 'success@simulator.amazonses.com'));
        $this->assertTrue($form->isValid());
        $this->assertCount(0, $form->getMessages());
    }

    public function testIsInvalid_incorrectFormatEmailAddress_shouldDisplayErrors()
    {
        $form = $this->buildController();
        $form->setData($this->setDataValues('dymmy', 'dymmy'));
        $this->assertFalse($form->isValid());
        $this->assertCount(2, $form->getMessages());
        $this->assertSame(
            ChangeEmailForm::MSG_INVALID_EMAIL_ERROR,
            $form->getMessages()['email'][0]
        );
        $this->assertSame(
            ChangeEmailForm::MSG_INVALID_EMAIL_ERROR,
            $form->getMessages()['emailConfirm'][0]
        );
    }

    public function testIsInvalid_emailsDoNotMatch_shouldDisplayErrors()
    {
        $form = $this->buildController();
        $form->setData($this->setDataValues('success+1@simulator.amazonses.com', 'success+2@simulator.amazonses.com'));
        $this->assertFalse($form->isValid());
        $this->assertCount(1, $form->getMessages());
        $this->assertSame(
            ChangeEmailForm::MSG_EMAILS_DONT_MATCH_ERROR,
            $form->getMessages()['emailConfirm'][0]
        );
    }

    public function testIsInvalid_emailAddressExceedsMaxLength_shouldDisplayErrors()
    {
        $longEmail = str_repeat('dummy', 50) . '@email.com';
        $form = $this->buildController();
        $form->setData($this->setDataValues($longEmail, $longEmail));
        $this->assertFalse($form->isValid());
        $this->assertCount(2, $form->getMessages());
        $this->assertSame(
            ChangeEmailForm::MSG_MAX_LENGTH_ERROR,
            $form->getMessages()['email'][0]
        );
        $this->assertSame(
            ChangeEmailForm::MSG_MAX_LENGTH_ERROR,
            $form->getMessages()['emailConfirm'][0]
        );
    }

    public function testIsInvalid_emailAddressIsBlank_shouldDisplayErrors()
    {
        $form = $this->buildController();
        $form->setData($this->setDataValues('', ''));
        $this->assertFalse($form->isValid());
        $this->assertCount(2, $form->getMessages());
        $this->assertSame(
            ChangeEmailForm::MSG_BLANK_EMAIL_ERROR,
            $form->getMessages()['email'][0]
        );
        $this->assertSame(
            ChangeEmailForm::MSG_BLANK_EMAIL_ERROR,
            $form->getMessages()['emailConfirm'][0]
        );
    }

    private function setDataValues($emailOne, $emailTwo)
    {
        return [
            'email' => $emailOne,
            'emailConfirm' => $emailTwo
        ];
    }

    private function buildController($email = null)
    {
        return new ChangeEmailForm($email);
    }
}