<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Person\PersonDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\Person;

/**
 * Class PersonWithAddressMapper.
 */
class PersonWithAddressMapper extends AbstractApiMapper
{
    private $personMapper;

    public function __construct()
    {
        $this->personContactMapper = new PersonContactMapper();
        $this->personMapper = new PersonMapper();
    }

    /**
     * @param $positions Person[]
     *
     * @return PersonDto[]
     */
    public function manyToDto($positions)
    {
        return parent::manyToDto($positions);
    }

    /**
     * @param Person $person
     *
     * @return PersonDto
     */
    public function toDto($person)
    {
        $personDto = $this->personMapper->toDto($person);
        $contactsDto = $this->personContactMapper->manyToDto($person->getContacts());

        $personDto->setContacts($contactsDto);

        return $personDto;
    }
}
