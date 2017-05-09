<?php

namespace SiteApi\Model;

use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;

/**
 * Class SitePersonnel.
 */
class SitePersonnel
{
    /** SiteBusinessRoleMap[] */
    private $positions;

    private $site;

    /**
     * @param Site $site
     * @param      $positions
     */
    public function __construct(Site $site, $positions)
    {
        $this->site = $site;
        $this->positions = $positions;
    }

    /**
     * @return SiteBusinessRoleMap[]
     */
    public function getPositions()
    {
        return $this->positions;
    }

    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param Person $person
     *
     * @return SiteBusinessRoleMap[]
     */
    public function getPositionsForPerson(Person $person)
    {
        $predicate = function (SiteBusinessRoleMap $position) use ($person) {
            return $person === $position->getPerson();
        };

        $positions = array_filter($this->positions, $predicate);

        return $positions;
    }

    /**
     * @param Person $person
     *
     * @return SiteBusinessRoleMap[]
     */
    public function getRolesForPerson(Person $person)
    {
        $positions = $this->getPositionsForPerson($person);
        $selector = function (SiteBusinessRoleMap $position) use ($person) {
            return $position->getSiteBusinessRole();
        };

        return ArrayUtils::map($positions, $selector);
    }

    /**
     * @param Person           $person
     * @param SiteBusinessRole $role
     *
     * @return SiteBusinessRoleMap
     */
    public function findPosition(Person $person, SiteBusinessRole $role)
    {
        $personRoles = $this->getPositionsForPerson($person);
        $predicate = function (SiteBusinessRoleMap $existingPosition) use ($role) {
            return $existingPosition->getSiteBusinessRole()->getCode() === $role->getCode();
        };

        return ArrayUtils::firstOrNull($personRoles, $predicate);
    }
}
