<?php

namespace DvsaClient\Entity;

/**
 * Class User.
 *
 * @deprecated Use DvsaClient\Entity\Person instead
 */
class User extends Person
{
    private $id;
    private $username;

    /**
     * @param string $id
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $username
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getUsername()
    {
        return $this->username;
    }
}
