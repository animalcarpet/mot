<?php

namespace DvsaCommon\ApiClient\Site\Dto;

use DvsaCommon\DtoSerialization\ReflectiveDtoInterface;

class GroupAssessmentListItem implements ReflectiveDtoInterface
{
    /** @var  int */
    private $userId;

    /** @var  string */
    private $username;

    /** @var  string */
    private $userFirstName;

    /** @var  string */
    private $userMiddleName;

    /** @var  string */
    private $userFamilyName;

    /** @var  \DateTime */
    private $dateAwarded;

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return GroupAssessmentListItem
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
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
     * @param string $username
     * @return GroupAssessmentListItem
     */
    public function setUsername($username)
    {
        $this->username = $username;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserFirstName()
    {
        return $this->userFirstName;
    }

    /**
     * @param string $userFirstName
     * @return GroupAssessmentListItem
     */
    public function setUserFirstName($userFirstName)
    {
        $this->userFirstName = $userFirstName;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateAwarded()
    {
        return $this->dateAwarded;
    }

    /**
     * @param \DateTime $dateAwarded
     * @return GroupAssessmentListItem
     */
    public function setDateAwarded($dateAwarded)
    {
        $this->dateAwarded = $dateAwarded;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserMiddleName()
    {
        return $this->userMiddleName;
    }

    /**
     * @param string $userMiddleName
     * @return GroupAssessmentListItem
     */
    public function setUserMiddleName($userMiddleName)
    {
        $this->userMiddleName = $userMiddleName;
        return $this;
    }

    /**
     * @return string
     */
    public function getUserFamilyName()
    {
        return $this->userFamilyName;
    }

    /**
     * @param string $userFamilyName
     * @return GroupAssessmentListItem
     */
    public function setUserFamilyName($userFamilyName)
    {
        $this->userFamilyName = $userFamilyName;
        return $this;
    }
}