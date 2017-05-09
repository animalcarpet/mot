<?php

namespace Site\ViewModel\Role;

/**
 * Class RemoveRoleConfirmationViewModel.
 */
class RemoveRoleConfirmationViewModel
{
    /** @var string */
    private $roleName;

    /** @var $siteName string */
    private $siteName;

    /** @var $employeeName string */
    private $employeeName;

    /** @var $employeeId int */
    private $employeeId;

    /** @var $siteId int */
    private $siteId;

    /** @var $positionId int */
    private $positionId;

    /** @var $activeMotTestNumber string */
    private $activeMotTestNumber;

    /**
     * @param int $activeMotTestNumber
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setActiveMotTestNumber($activeMotTestNumber)
    {
        $this->activeMotTestNumber = $activeMotTestNumber;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasActiveMotTest()
    {
        if (is_null($this->activeMotTestNumber)) {
            return false;
        }

        return true;
    }

    /**
     * @return bool
     */
    public function canRoleBeRemoved()
    {
        return !$this->hasActiveMotTest();
    }

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
     * @param int $siteId
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setSiteId($siteId)
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * @return int
     */
    public function getSiteId()
    {
        return $this->siteId;
    }

    /**
     * @param string $siteName
     *
     * @return RemoveRoleConfirmationViewModel
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteName()
    {
        return $this->siteName;
    }

    /**
     * @param int $positionId
     *
     * @return RemoveRoleConfirmationViewModel
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
     * @return RemoveRoleConfirmationViewModel
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
