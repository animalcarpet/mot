<?php

namespace AccountTest\Logic;

use Account\Logic\PasswordPolicy;
use Account\ViewModel\ChangePasswordFormModel;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommonTest\TestUtils\XMock;

class PasswordPolicyTest extends \PHPUnit_Framework_TestCase
{
    /** @var PasswordPolicyTestFormModel */
    private $formModel;

    public function setUp()
    {
        $this->formModel = XMock::of(PasswordPolicyTestFormModel::class, ['isValid', 'addError', 'hasErrors']);
    }

    public function testPasswordIsNotUsernameRaisesError()
    {
        $policy = new PasswordPolicy(
            $this->formModel,
            'theUsername1',
            'theUsername1',
            'theUsername1'
        );

        $this->formModel->expects($this->once())
            ->method('addError')
            ->with(
                ChangePasswordFormModel::FIELD_PASS,
                PasswordPolicy::ERR_NOT_USERNAME
            );

        $this->assertFalse($policy->enforce());
    }

    public function testEmptyFirstPasswordFailsPolicyCheck()
    {
        $policy = new PasswordPolicy(
            $this->formModel,
            'theusername',
            '',
            'idontmatterforthistest'
        );

        $this->formModel->expects($this->once())
            ->method('addError')
            ->with(
                ChangePasswordFormModel::FIELD_PASS,
                PasswordPolicy::ERR_REQUIRED
            );

        $this->assertFalse($policy->enforce());
    }

    public function testFailsIfPasswordsAreDifferent()
    {
        $policy = new PasswordPolicy(
            $this->formModel,
            'theusername',
            'password-A1',
            'password-A2'
        );

        $this->formModel->expects($this->once())
            ->method('addError')
            ->with(
                ChangePasswordFormModel::FIELD_PASS_CONFIRM,
                PasswordInputFilter::MSG_PASSWORD_CONFIRM_DIFFER
            );

        $this->assertFalse($policy->enforce());
    }
}
