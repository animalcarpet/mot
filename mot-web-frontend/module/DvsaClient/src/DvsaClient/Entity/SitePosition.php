<?php

namespace DvsaClient\Entity;

use DvsaCommon\Enum\BusinessRoleStatusCode;

/**
 * Class representing position at site (nominated role at site).
 */
class SitePosition
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var Person
     */
    private $person;

    /**
     * @var string
     */
    private $roleCode;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string [null or Y-d-m h:i:s]
     */
    private $actionedOn;

    /**
     * @param string $roleCode
     *
     * @see SiteBusinessRoleCode
     *
     * @return SitePosition
     */
    public function setRoleCode($roleCode)
    {
        $this->roleCode = $roleCode;

        return $this;
    }

    /**
     * @return string
     *
     * @see SiteBusinessRoleCode
     */
    public function getRoleCode()
    {
        return $this->roleCode;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param Person $person
     *
     * @return SitePosition
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    public function getActionedOn()
    {
        return $this->actionedOn;
    }

    /**
     * @param string $actionedOn
     *
     * @return SitePosition
     */
    public function setActionedOn($actionedOn)
    {
        $this->actionedOn = $actionedOn;

        return $this;
    }

    /**
     * @param int $id
     *
     * @return SitePosition
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function isPending()
    {
        return BusinessRoleStatusCode::PENDING === $this->getStatus();
    }

    public function isActive()
    {
        return BusinessRoleStatusCode::ACTIVE === $this->getStatus();
    }
}
