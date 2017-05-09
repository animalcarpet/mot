<?php

namespace Site\Model;

use DvsaClient\Entity\Person;
use DvsaClient\Entity\SitePosition;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class SitePersonnel.
 */
class SitePersonnel
{
    /**
     * @var SitePosition[][]
     */
    private $positions;

    /**
     * @var Person[]
     */
    private $persons = [];

    private $personCount;

    public function getPositions()
    {
        return $this->positions;
    }

    public function getPersons()
    {
        return $this->persons;
    }

    /**
     * @param $positions SitePosition[]
     */
    public function __construct($positions)
    {
        $this->positions = $positions;
        $this->extractPersons();
        $this->arrangePositionsByPersonId();
    }

    private function extractPersons()
    {
        foreach ($this->positions as $position) {
            $person = $position->getPerson();
            $this->persons[$person->getId()] = $person;
        }

        $this->personCount = count($this->persons);
    }

    private function arrangePositionsByPersonId()
    {
        $arranged = [];

        foreach ($this->persons as $person) {
            $arranged[$person->getId()] = [];
        }

        foreach ($this->positions as $position) {
            $personId = $position->getPerson()->getId();
            $arranged[$personId][] = $position;
        }

        $this->positions = $arranged;
    }

    /**
     * @param Person $person
     *
     * @return SitePosition[]
     */
    public function getPositionsForPerson(Person $person)
    {
        return $this->positions[$person->getId()];
    }

    public function getRolesForPerson(Person $person)
    {
        return ArrayUtils::map(
            $this->getPositionsForPerson($person),
            function (SitePosition $position) {
                return $position->getRoleCode();
            }
        );
    }

    /**
     * Number of people working in site.
     *
     * @return int
     */
    public function getPersonCount()
    {
        return $this->personCount;
    }
}
