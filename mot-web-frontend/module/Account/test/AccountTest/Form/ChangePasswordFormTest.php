<?php

namespace AccountTest\Form;

use Dashboard\Form\ChangePasswordForm as Form;
use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use Zend\Validator\NotEmpty;
use PHPUnit_Framework_TestCase;
use Dvsa\OpenAM\OpenAMClientInterface;

class ChangePasswordFormTest extends PHPUnit_Framework_TestCase
{
    public function testFormReturnsErrorMessageWhenOldPasswordIsInvalid()
    {
        $data = [
            Form::FIELD_OLD_PASSWORD => '',
            Form::FIELD_PASSWORD => 'Password1',
            Form::FIELD_RETYPE_PASSWORD => 'Password1',
        ];

        $identity = XMock::of(MotIdentityProviderInterface::class, ['getUsername']);
        $identity
            ->expects($this->any())
            ->method('getUsername')
            ->willReturn('tester1');

        $identityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        $form = new Form($identityProvider, XMock::of(OpenAMClientInterface::class), 'mot');
        $form->setData($data);

        $this->assertFalse($form->isValid());

        $this->assertEquals(1, count($form->getMessages()));

        $msg = $form->getMessages(ChangePasswordInputFilter::FIELD_OLD_PASSWORD);
        $this->assertEquals(ChangePasswordInputFilter::MSG_OLD_PASSWORD_EMPTY, $msg[NotEmpty::IS_EMPTY]);
    }
}
