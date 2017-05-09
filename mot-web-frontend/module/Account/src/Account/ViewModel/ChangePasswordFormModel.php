<?php

namespace Account\ViewModel;

use Account\Logic\PasswordPolicy;
use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Model for change password form.
 */
class ChangePasswordFormModel extends AbstractFormModel
{
    const FIELD_PASS = 'password';
    const FIELD_PASS_CONFIRM = 'passwordConfirm';
    const ERR_REQUIRED = 'Password is required';
    const ERR_SAME_AS_NAME = 'The password can not be the same as username';
    const ERR_NOT_SAME = 'The passwords you have entered do not match';
    const ERR_NOT_STRONG = 'The password you have entered is not strong enough';
    const ERR_NOT_USERNAME = 'Your password must not match your user ID';

    /** @var string */
    private $password;
    /** @var string */
    private $passwordConfirm;
    /** @var string */
    private $username;

    /** @var PasswordPolicy */
    private $policy;

    /** @var bool $tryAgainLink */
    private $tryAgainLink;

    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPasswordConfirm()
    {
        return $this->passwordConfirm;
    }

    public function setPasswordConfirm($pass)
    {
        $this->passwordConfirm = $pass;

        return $this;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function isValid()
    {
        return  $this->policy->enforce();
    }

    public function populateFromPost(array $postData)
    {
        $this->setPassword(ArrayUtils::tryGet($postData, self::FIELD_PASS));
        $this->setPasswordConfirm(ArrayUtils::tryGet($postData, self::FIELD_PASS_CONFIRM));

        $this->policy = new PasswordPolicy(
            $this,
            $this->getUsername(),
            $this->getPassword(),
            $this->getPasswordConfirm()
        );

        return $this;
    }

    public function setTryAgainLink($value)
    {
        $this->tryAgainLink = $value;
    }

    public function isTryAgainLink()
    {
        return $this->tryAgainLink ? true : false;
    }
}
