<?php

namespace Account\ViewModel;

use DvsaClient\ViewModel\AbstractFormModel;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class ResetViewModel
 * @package Account\ViewModel
 */
class PasswordResetFormModel extends AbstractFormModel
{
    const FIELD_USERNAME            = 'username';
    const USER_NOT_FOUND            = 'This user ID does not match our records';
    const USER_REQUIRED             = 'User Id is required';

    /** @var string $username */
    private $username;
    /** @var array $config */
    private $config;
    /** @var integer $cfgExpireTime */
    private $cfgExpireTime;
    /** @var string $email */
    private $email;

    /**
     * @return string
     */
    public function getUsername()
    {
        return trim($this->username);
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return int
     */
    public function getCfgExpireTime()
    {
        return $this->cfgExpireTime;
    }

    /**
     * @param int $cfgExpireTime
     *
     * @return $this
     */
    public function setCfgExpireTime($cfgExpireTime)
    {
        $this->cfgExpireTime = $cfgExpireTime;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $userName = $this->getUsername();
        if (empty($userName)) {
            $this->addError(self::FIELD_USERNAME, self::USER_REQUIRED);

            return false;
        }

        return !$this->hasErrors();
    }

    /**
     * @param array $postData
     *
     * @return $this
     */
    public function populateFromPost(array $postData)
    {
        $this->setUsername(ArrayUtils::tryGet($postData, self::FIELD_USERNAME));
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentPage()
    {
        return AccountUrlBuilderWeb::forgottenPassword();
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param array $config
     *
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getObscuredEmailAddress()
    {
        $numberOfVisibleCharactersInLocalPart = 3;

        $positionOfLastAtSign = strrpos($this->email, '@');
        $localPartVisible = substr($this->email, 0, min($numberOfVisibleCharactersInLocalPart, $positionOfLastAtSign));
        $localPartObscured = str_repeat('•', $positionOfLastAtSign - strlen($localPartVisible));
        $domain = substr($this->email, $positionOfLastAtSign, strlen($this->email));

        return $localPartVisible . $localPartObscured . $domain;
    }
}
