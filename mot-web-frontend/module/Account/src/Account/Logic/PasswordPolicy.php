<?php

namespace Account\Logic;

use DvsaClient\ViewModel\AbstractFormModel;
use Account\ViewModel\ChangePasswordFormModel;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaCommon\Utility\ArrayUtils;

/**
 * This class seeks to act as a mediator between OpenAM password policy and any other
 * transient solutions. It will perform any required checks on the string passed in
 * as the tentative new password and indicate if it meets all the required checks.
 *
 * By encapsulating policy here we can accommodate OpenAM API calls from here in the
 * future should we need to do so.
 */
class PasswordPolicy
{
    const ERR_REQUIRED = 'Password is required';
    const ERR_SAME_AS_NAME = 'The password can not be the same as username';
    const ERR_NOT_SAME = 'The passwords you have entered do not match';
    const ERR_NOT_STRONG = 'The password you have entered is not strong enough';
    const ERR_NOT_USERNAME = 'Your password must not match your user ID';

    /** @var AbstractFormModel -> delegate error handler */
    protected $errorHandler;

    /** @var string the current username for this activation link */
    protected $username;

    /** @var string the candidate new password value */
    protected $password;

    /** @var string the confirmation value */
    protected $password2;

    /**
     * Constructs the policy checker. The username is required so we can reject that as
     * a potential new password value.
     *
     * @param AbstractFormModel $errorHandler
     * @param string            $username     the associated username for this reset link sessin
     * @param string            $password     the new password value
     * @param string            $confirm      is the confirmation password value
     */
    public function __construct(AbstractFormModel $errorHandler, $username, $password, $confirm)
    {
        $this->errorHandler = $errorHandler;
        $this->username = $username;
        $this->password = $password;
        $this->password2 = $confirm;
        $this->errors = [];
    }

    /**
     * Answers true if the password meets all required policy checks.
     *
     * @return bool
     */
    public function enforce()
    {
        if ($this->password) {
            if (!$this->verifyPasswordQuality()) {
                return false;
            }

            if ($this->username == $this->password) {
                $this->errorHandler->addError(
                    ChangePasswordFormModel::FIELD_PASS,
                    self::ERR_NOT_USERNAME
                );

                return false;
            } elseif ($this->password != $this->password2) {
                $this->errorHandler->addError(
                    ChangePasswordFormModel::FIELD_PASS_CONFIRM,
                    self::ERR_NOT_SAME
                );

                return false;
            }
        } elseif (empty($this->password)) {
            $this->errorHandler->addError(
                ChangePasswordFormModel::FIELD_PASS,
                self::ERR_REQUIRED
            );

            return false;
        }

        return true;
    }

    private function verifyPasswordQuality()
    {
        $filter = new PasswordInputFilter();
        $filter->init();

        $filter->setData([
            PasswordInputFilter::FIELD_PASSWORD => $this->password,
            PasswordInputFilter::FIELD_PASSWORD_CONFIRM => $this->password2,
        ]);
        if (!$filter->isValid()) {
            $messages = $filter->getMessages();

            // every field can have many errors, let's just select first error per field
            $messages = ArrayUtils::mapWithKeys(
                $messages,
                function ($key, $value) {
                    return $key;
                },
                function ($key, $value) {
                    return ArrayUtils::firstOrNull($value);
                }
            );

            if (array_key_exists(PasswordInputFilter::FIELD_PASSWORD, $messages)) {
                $this->errorHandler->addError(
                    ChangePasswordFormModel::FIELD_PASS,
                    $messages[PasswordInputFilter::FIELD_PASSWORD]
                );
            }

            if (array_key_exists(PasswordInputFilter::FIELD_PASSWORD_CONFIRM, $messages)) {
                $this->errorHandler->addError(
                    ChangePasswordFormModel::FIELD_PASS_CONFIRM,
                    $messages[PasswordInputFilter::FIELD_PASSWORD_CONFIRM]
                );
            }

            return false;
        }

        return true;
    }
}
