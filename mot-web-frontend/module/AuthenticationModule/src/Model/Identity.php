<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;

use DvsaCommon\Model\PersonAuthorization;

/**
 * Class Identity.
 */
class Identity implements MotFrontendIdentityInterface
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $displayName;

    /**
     * @var
     */
    private $displayRole;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var VehicleTestingStation;
     */
    private $currentVts;

    /**
     * @var bool
     */
    private $accountClaimRequired = false;

    /**
     * @var bool
     */
    private $passwordChangeRequired = false;

    /**
     * @var bool
     */
    private $secondFactorRequired = false;

    /**
     * By default we can assume the password has expired.
     * It will be verified by ExpiredPasswordListener.
     *
     * @var bool
     */
    private $passwordExpired = true;

    /**
     * @var \DateTime
     */
    private $passwordExpiryDate;

    /**
     * @var bool
     */
    private $authenticatedWith2FA = false;

    /**
     * @var bool
     */
    private $authenticatedWithLostForgotten = false;

    /**
     * @return PersonAuthorization
     *
     * @deprecated will be removed - do not use
     */
    public function getPersonAuthorization()
    {
        return $this->personAuthorization;
    }

    /**
     * @param PersonAuthorization $personAuthorization
     *
     * @deprecated will be removed - do not use
     */
    public function setPersonAuthorization($personAuthorization)
    {
        $this->personAuthorization = $personAuthorization;

        return $this;
    }

    /**
     * TO BE REMOVED AND EXPLICITLY STORED IN SESSION.
     *
     * @var PersonAuthorization
     */
    private $personAuthorization;

    /**
     * @param int $userId
     *
     * @return Identity
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param string $displayName
     *
     * @return $this
     */
    public function setDisplayName($displayName)
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->displayName;
    }

    /**
     * @param string $displayRole
     *
     * @return $this
     */
    public function setDisplayRole($displayRole)
    {
        $this->displayRole = $displayRole;

        return $this;
    }

    /**
     * @return string
     */
    public function getDisplayRole()
    {
        return $this->displayRole;
    }

    /**
     * @param string $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $accessToken
     *
     * @return $this
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation $currentVts
     *
     * @return Identity
     */
    public function setCurrentVts(VehicleTestingStation $currentVts = null)
    {
        $this->currentVts = $currentVts;

        return $this;
    }

    /**
     * @return \Dvsa\Mot\Frontend\AuthenticationModule\Model\VehicleTestingStation
     */
    public function getCurrentVts()
    {
        return $this->currentVts;
    }

    public function clearCurrentVts()
    {
        $this->currentVts = null;
    }

    public function setAccountClaimRequired($accountClaimRequired)
    {
        $this->accountClaimRequired = $accountClaimRequired;

        return $this;
    }

    public function isAccountClaimRequired()
    {
        return $this->accountClaimRequired;
    }

    public function setPasswordChangeRequired($passwordChangeRequired)
    {
        $this->passwordChangeRequired = $passwordChangeRequired;

        return $this;
    }

    /**
     * @return bool
     */
    public function isSecondFactorRequired()
    {
        return $this->secondFactorRequired;
    }

    /**
     * @param $secondFactorRequired
     *
     * @return $this
     */
    public function setSecondFactorRequired($secondFactorRequired)
    {
        $this->secondFactorRequired = $secondFactorRequired;

        return $this;
    }

    public function isPasswordChangeRequired()
    {
        return $this->passwordChangeRequired;
    }

    public function getUuid()
    {
        return $this->username;
    }

    public function hasPasswordExpired()
    {
        return $this->passwordExpired;
    }

    public function setPasswordExpired($passwordExpired)
    {
        $this->passwordExpired = $passwordExpired;

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
     * @param \DateTime $passwordExpiryDate
     *
     * @return Identity
     */
    public function setPasswordExpiryDate($passwordExpiryDate)
    {
        $this->passwordExpiryDate = $passwordExpiryDate;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticatedWith2FA()
    {
        return $this->authenticatedWith2FA;
    }

    /**
     * @param $authenticatedWith2FA
     *
     * @return $this
     */
    public function setAuthenticatedWith2FA($authenticatedWith2FA)
    {
        $this->authenticatedWith2FA = $authenticatedWith2FA;

        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthenticatedWithLostForgotten()
    {
        return $this->authenticatedWithLostForgotten;
    }

    /**
     * @param $authenticatedWithLostForgotten
     *
     * @return $this
     */
    public function setAuthenticatedWithLostForgotten($authenticatedWithLostForgotten)
    {
        $this->authenticatedWithLostForgotten = $authenticatedWithLostForgotten;

        return $this;
    }
}
