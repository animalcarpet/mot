<?php

namespace Organisation\ViewModel\View\Role;

/**
 * Class RemoveRoleConfirmationViewModel.
 */
class RemoveRoleConfirmationViewModel
{
    /** @var $roleName string */
    private $roleName;

    /** @var $organisationName string */
    private $organisationName;

    /** @var $employeeName string */
    private $employeeName;

    /** @var $employeeId int */
    private $employeeId;

    /** @var $organisationId int */
    private $organisationId;

    /** @var $roleId int */
    private $roleId;

    /**
     * @param string $employeeName
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setEmployeeName($employeeName)
    {
        $this->employeeName = $employeeName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmployeeName()
    {
        return $this->employeeName;
    }

    /**
     * @param int $organisationId
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setOrganisationId($organisationId)
    {
        $this->organisationId = $organisationId;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrganisationId()
    {
        return $this->organisationId;
    }

    /**
     * @param string $organisationName
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setOrganisationName($organisationName)
    {
        $this->organisationName = $organisationName;

        return $this;
    }

    /**
     * @return string
     */
    public function getOrganisationName()
    {
        return $this->organisationName;
    }

    /**
     * @param int $roleId
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setRoleId($roleId)
    {
        $this->roleId = $roleId;

        return $this;
    }

    /**
     * @return int
     */
    public function getRoleId()
    {
        return $this->roleId;
    }

    /**
     * @param mixed $roleName
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setRoleName($roleName)
    {
        $this->roleName = $roleName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoleName()
    {
        return $this->roleName;
    }

    /**
     * @param int $employeeId
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;

        return $this;
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }
}
