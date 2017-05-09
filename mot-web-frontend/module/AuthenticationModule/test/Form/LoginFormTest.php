<?php

namespace Dvsa\Mot\Frontend\AuthenticationModuleTest\Form;

use Dvsa\Mot\Frontend\AuthenticationModule\Form\LoginForm;

class LoginFormTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoginForm */
    private $form;

    public function setUp()
    {
        $this->form = new LoginForm();
    }

    public function testValidate_userIdIsBlank_expectErrorMessage()
    {
        $this->bindAndValidate('', 'SomePassword');
        $userIdMessages = $this->messagesFor($this->form->getUsernameField());
        $this->assertEquals('Enter your User ID', array_pop($userIdMessages));
    }

    public function testValidate_passwordIsBlank_expectErrorMessage()
    {
        $this->bindAndValidate('SomeUserId', '');
        $passwordMessages = $this->messagesFor($this->form->getPasswordField());
        $this->assertEquals('Enter your password', array_pop($passwordMessages));
    }

    public function testValidate_userIdIsEmail_expectErrorMessage()
    {
        $this->bindAndValidate('someone@home.com', 'SomePassword');
        $userIdMessages = $this->messagesFor($this->form->getUsernameField());
        $this->assertEquals('Enter a valid User ID. For example: SMIT1234', array_pop($userIdMessages));
    }

    private function bindAndValidate($username, $password)
    {
        $this->form->setData([LoginForm::USERNAME => $username, LoginForm::PASSWORD => $password]);
        $this->form->isValid();
    }

    private function messagesFor($field)
    {
        return array_values($this->form->getMessages($field->getName()));
    }
}
