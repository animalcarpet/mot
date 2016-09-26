<?php

namespace Dvsa\Mot\Frontend\PersonModuleTest\Form;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Form\ChangeSecurityQuestionsPasswordForm;
use Zend\Form\Element\Text;

class ChangeSecurityQuestionsPasswordFormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ChangeSecurityQuestionsPasswordForm $form
     */
    private $form;

    public function setUp()
    {
        $this->form = new ChangeSecurityQuestionsPasswordForm();
    }

    public function testFormIsNotValidWhenPasswordEmpty()
    {
        $data = [ChangeSecurityQuestionsPasswordForm::FIELD_PASSWORD => ''];
        $this->form->setData($data);
        $this->assertFalse($this->form->isValid());
    }

    public function testWhenFormInvalidMessageCorrectlyPopulated()
    {
        $data = [ChangeSecurityQuestionsPasswordForm::FIELD_PASSWORD => ''];
        $this->form->setData($data);

        $this->form->isValid();

        $actual = $this->form->getMessages(ChangeSecurityQuestionsPasswordForm::FIELD_PASSWORD);
        $this->assertCount(1, $actual);
        $this->assertSame(ChangeSecurityQuestionsPasswordForm::MSG_EMPTY_PASSWORD, $actual[0]);
    }

    public function testFromIsValidWhenPasswordEntered()
    {
        $data = [ChangeSecurityQuestionsPasswordForm::FIELD_PASSWORD => 'password'];
        $this->form->setData($data);
        $this->assertTrue($this->form->isValid());
        $this->assertEmpty($this->form->getMessages());
    }

    public function testCanGetPasswordField()
    {
        $passwordField = $this->form->getPassword();
        $this->assertInstanceOf(Text::class, $passwordField);
    }
}
