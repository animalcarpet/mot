<?php

namespace DvsaAuthentication;

use DvsaCommon\Auth\MotIdentityInterface;
use DvsaEntities\Entity\Person;

/**
 * Wrapper for the identity used by the authentication system.
 */
class Identity implements MotIdentityInterface
{
    /**
     * @var string|null
     */
    protected $token;

    /**
     * @var string|null
     */
    protected $uuid;

    /**
     * @var \DateTime
     */
    protected $passwordExpiryDate;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $displayName;

    /**
     * @var bool
     */
    protected $isAccountClaimRequired;

    /**
     * @var bool
     */
    protected $isPasswordChangeRequired;

    /**
     * @var bool
     */
    protected $isSecondFactorRequired;

    /**
     * @var Person
     */
    private $person;

    public function __construct(Person $person)
    {
        $this->userId = $person->getId();
        $this->username = $person->getUsername();
        $this->displayName = $person->getDisplayName();
        $this->isAccountClaimRequired = $person->isAccountClaimRequired();
        $this->isPasswordChangeRequired = $person->isPasswordChangeRequired();
        $this->isSecondFactorRequired = $person->getAuthenticationMethod()->isCard();
        $this->person = $person;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string|null
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return bool
     */
    public function isPasswordChangeRequired()
    {
        return $this->isPasswordChangeRequired;
    }

    /**
     * @return bool
     */
    public function isSecondFactorRequired()
    {
        return $this->isSecondFactorRequired;
    }

    /**
     * @return bool
     */
    public function isAccountClaimRequired()
    {
        return $this->isAccountClaimRequired;
    }

    /**
     * @return null|string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param string $uuid
     *
     * @return self
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @param \DateTime $passwordExpiryDate
     *
     * @return $this
     */
    public function setPasswordExpiryDate(\DateTime $passwordExpiryDate)
    {
        $this->passwordExpiryDate = $passwordExpiryDate;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPasswordExpiryDate()
    {
        return $this->passwordExpiryDate;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param $isFeatureToggleRequired
     *
     * @return $this
     */
    public function setIsSecondFactorRequired($isSecondFactorRequired)
    {
        $this->isSecondFactorRequired = $isSecondFactorRequired;

        return $this;
    }
}
