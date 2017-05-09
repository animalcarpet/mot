<?php

namespace TestSupport\Model;

use DvsaCommon\Utility\ArrayUtils;

/**
 * Model of account in the system.
 */
class Account
{
    /**
     * @var int
     */
    private $personId;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $firstName;

    /**
     * @var string
     */
    private $surname;

    public function __construct($data)
    {
        if (false === is_array($data)) {
            throw new \InvalidArgumentException('Expected array, '.gettype($data).' given.');
        }

        $this->setPersonId(ArrayUtils::get($data, 'personId'));
        $this->setUsername(ArrayUtils::tryGet($data, 'username', ''));
        $this->setPassword(ArrayUtils::tryGet($data, 'password', ''));
        $this->setFirstName(ArrayUtils::tryGet($data, 'firstName', ''));
        $this->setSurname(ArrayUtils::tryGet($data, 'surname', ''));
    }

    /**
     * @param string $password
     *
     * @return Account
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param int $personId
     *
     * @return Account
     */
    public function setPersonId($personId)
    {
        $this->personId = $personId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return $this->personId;
    }

    /**
     * @param string $username
     *
     * @return Account
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
     * @param string $firstName
     *
     * @return Account
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
     * @param string $surname
     *
     * @return Account
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return string
     */
    public function getSurname()
    {
        return $this->surname;
    }
}
