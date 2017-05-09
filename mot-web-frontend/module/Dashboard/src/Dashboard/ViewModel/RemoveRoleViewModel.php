<?php

namespace Dashboard\ViewModel;

/**
 * Class RemoveRoleViewModel.
 */
class RemoveRoleViewModel
{
    /** @var string */
    private $roleName;

    /** @var $entityName string */
    private $entityName;

    /** @var $personId int */
    private $personId;

    /** @var $positionId int */
    private $positionId;

    /** @var $entityId int */
    private $entityId;

    /**
     * @param string $entityName
     *
     * @return RemoveRoleViewModel
     */
    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @param int $positionId
     *
     * @return RemoveRoleViewModel
     */
    public function setPositionId($positionId)
    {
        $this->positionId = $positionId;

        return $this;
    }

    /**
     * @return int
     */
    public function getPositionId()
    {
        return $this->positionId;
    }

    /**
     * @param string $roleName
     *
     * @return RemoveRoleViewModel
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @param int $personId
     *
     * @return RemoveRoleViewModel
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
     * @return int
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     *
     * @return $this
     */
    public function setEntityId($entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }
}
