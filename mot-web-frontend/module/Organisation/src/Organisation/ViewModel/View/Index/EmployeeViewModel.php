<?php

namespace Organisation\ViewModel\View\Index;

use DvsaCommon\Dto\Organisation\OrganisationPositionDto;
use DvsaCommon\Dto\Person\PersonDto;

/**
 * Class Employee.
 */
class EmployeeViewModel
{
    private $person;

    /**
     * @var OrganisationPositionDto[]
     */
    private $positions = [];

    /** @var $positionId int */
    private $positionId;

    public function __construct(PersonDto $user)
    {
        $this->person = $user;
    }

    public function getPositions()
    {
        return $this->positions;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function addPosition(OrganisationPositionDto $role)
    {
        $this->positions[] = $role;
    }

    public function getDisplayRoles()
    {
        $displayRoles = [];

        foreach ($this->positions as $position) {
            $displayRoles[] = $position->getRole();
        }

        $displayRolesString = implode(', ', $displayRoles);

        return $displayRolesString;
    }

    /**
     * @param int $positionId
     *
     * @return $this
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
}
