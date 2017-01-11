<?php

namespace DvsaAuthentication\Model;

use DvsaCommon\Model\PersonAuthorization;

/**
 * Class Identity
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
     * @var $string
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
     * @return PersonAuthorization
     * @deprecated will be removed - do not use
     */
    public function getPersonAuthorization()
    {
        return $this->personAuthorization;
    }

    /**
     * @param PersonAuthorization $personAuthorization
     * @deprecated will be removed - do not use
     */
    public function setPersonAuthorization($personAuthorization)
    {
        $this->personAuthorization = $personAuthorization;
        return $this;
    }

    /**
     * TO BE REMOVED AND EXPLICITLY STORED IN SESSION
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
     * @param \DvsaAuthentication\Model\VehicleTestingStation $currentVts
     *
     * @return Identity
     */
    public function setCurrentVts(VehicleTestingStation $currentVts = null)
    {
        $this->currentVts = $currentVts;
        return $this;
    }

    /**
     * @return \DvsaAuthentication\Model\VehicleTestingStation
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

    public function isPasswordChangeRequired()
    {
        return $this->passwordChangeRequired;
    }

    public function getUuid(){
        return $this->username;
    }
}
