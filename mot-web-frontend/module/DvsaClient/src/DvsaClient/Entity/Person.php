<?php

namespace DvsaClient\Entity;

/**
 * Class Person.
 */
class Person
{
    private $username;
    private $firstName;
    private $middleName;
    private $familyName;
    private $contactDetails;
    private $id;
    private $uuid;

    /**
     * @return mixed
     */
    public function getUserReference()
    {
        return $this->userReference;
    }

    /**
     * @param mixed $userReference
     */
    public function setUserReference($userReference)
    {
        $this->userReference = $userReference;
    }
    private $userReference;

    /**
     * @param ContactDetail[] $contactDetails
     *
     * @return $this
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * @return ContactDetail[]
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * @param ContactDetail[] $contactDetails
     *
     * @return $this
     */
    public function setContacts($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * @return ContactDetail[]
     */
    public function getContacts()
    {
        return $this->contactDetails;
    }

    /**
     * @param string $familyName
     *
     * @return $this
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $uuid
     *
     * @return $this
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;

        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $middleName
     *
     * @return $this
     */
    public function setMiddleName($middleName)
    {
        $this->middleName = $middleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return $this->middleName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        $middleName = $this->getMiddleName();

        return $this->getFirstName().(empty($middleName) ? ' ' : ' '.$middleName.' ').$this->getFamilyName();
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
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }
}
