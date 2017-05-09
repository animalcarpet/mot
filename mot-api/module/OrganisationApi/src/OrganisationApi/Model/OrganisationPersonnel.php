<?php

namespace OrganisationApi\Model;

use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;

/**
 * Class OrganisationPersonnel.
 */
class OrganisationPersonnel
{
    /**
     * @var OrganisationBusinessRoleMap[]
     */
    private $positions;
    private $organisation;

    /**
     * @param   $organisation
     */
    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
        $this->positions = $organisation->getPositions()->toArray();
    }

    public function getPositions()
    {
        return $this->positions;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @param Person $person
     *
     * @return OrganisationBusinessRoleMap[]
     */
    public function getPositionsForPerson(Person $person)
    {
        $predicate = function (OrganisationBusinessRoleMap $position) use ($person) {
            return $person === $position->getPerson();
        };

        $positions = array_filter($this->positions, $predicate);

        return $positions;
    }

    /**
     * @param Person                   $person
     * @param OrganisationBusinessRole $role
     *
     * @return OrganisationBusinessRoleMap
     */
    public function findPosition(Person $person, OrganisationBusinessRole $role)
    {
        $personRoles = $this->getPositionsForPerson($person);
        $predicate = function (OrganisationBusinessRoleMap $existingPosition) use ($role) {
            return $existingPosition->getOrganisationBusinessRole()->getName() === $role->getName();
        };

        return ArrayUtils::firstOrNull($personRoles, $predicate);
    }
}
