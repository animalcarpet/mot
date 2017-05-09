<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Dto\Person\PersonContactDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\PersonContact;

/**
 * Class PersonContactMapper.
 */
class PersonContactMapper
{
    private $contactMapper;

    public function __construct()
    {
        $this->contactMapper = new ContactMapper();
    }

    /**
     * @param $contacts
     *
     * @return array
     */
    public function manyToArray($contacts)
    {
        $data = [];

        foreach ($contacts as $contact) {
            $data[] = $this->toArray($contact);
        }

        return $data;
    }

    /**
     * @param PersonContact $contact
     *
     * @return array
     */
    public function toArray(PersonContact $contact)
    {
        $contactData = $this->contactMapper->toArray($contact->getDetails());
        $contactData['type'] = $contact->getType()->getName();
        $contactData['_clazz'] = 'PersonContact';

        return $contactData;
    }

    /**
     * @param $contacts
     *
     * @return PersonContactDto[]
     */
    public function manyToDto($contacts)
    {
        return ArrayUtils::map(
            $contacts, function (PersonContact $contact) {
                return $this->toDto($contact);
            }
        );
    }

    /**
     * @param PersonContact $contact
     *
     * @return PersonContactDto
     */
    public function toDto(PersonContact $contact)
    {
        $contactDto = new PersonContactDto();
        $this->contactMapper->toDto($contact->getDetails(), $contactDto);

        $contactDto->setType($contact->getType()->getName());

        return $contactDto;
    }
}
