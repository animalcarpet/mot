<?php

namespace OrganisationApi\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Person\PersonDto;
use DvsaEntities\Entity\Person;

/**
 * Class PersonMapper.
 */
class PersonMapper
{
    /**
     * @param Person[] $persons
     *
     * @return array
     */
    public function manyToArray($persons)
    {
        $data = [];

        foreach ($persons as $person) {
            $data[] = $this->toArray($person);
        }

        return $data;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    public function toArray(Person $person)
    {
        $personData['id'] = $person->getId();
        $personData['userName'] = $person->getUsername();
        $personData['firstName'] = $person->getFirstName();
        $personData['middleName'] = $person->getMiddleName();
        $personData['familyName'] = $person->getFamilyName();
        $personData['gender'] = $person->getGender() ? $person->getGender()->getName() : '';
        $personData['title'] = $person->getTitle() && $person->getTitle()->getId() ? $person->getTitle()->getName() : '';

        // AEP hasn't got date of birth
        if ($person->getDateOfBirth()) {
            $personData['dateOfBirth'] = DateTimeApiFormat::date($person->getDateOfBirth());
        }

        $personData['_clazz'] = 'Person';

        return $personData;
    }

    public function toDto(Person $person)
    {
        $personDto = new PersonDto();
        $personDto->setId($person->getId());
        $personDto->setUsername($person->getUsername());
        $personDto->setDisplayName($person->getDisplayName());
        $personDto->setFirstName($person->getFirstName());
        $personDto->setMiddleName($person->getMiddleName());
        $personDto->setFamilyName($person->getFamilyName());
        $personDto->setGender($person->getGender() ? $person->getGender()->getName() : null);
        $personDto->setContactDetails($this->createContactDtoArray($person));

        $title = '';

        if ($person->getTitle() && $person->getTitle()->getId()) {
            $title = $person->getTitle()->getName();
        }

        $personDto->setTitle($title);

        if ($person->getDateOfBirth()) {
            $personDto->setDateOfBirth(DateTimeApiFormat::date($person->getDateOfBirth()));
        }

        return $personDto;
    }

    private function createContactDtoArray(Person $person)
    {
        $contactDtoArray = [];
        $contactDto = new ContactDto();
        $contactDto->setEmails($this->createEmailDtoArray($person));
        array_push($contactDtoArray, $contactDto);

        return $contactDtoArray;
    }

    private function createEmailDtoArray(Person $person)
    {
        $emailDto = new EmailDto();
        if (!empty($person)) {
            $emailDto->setEmail($person->getPrimaryEmail());
        }
        $emailDtoArray = [
            $emailDto,
        ];

        return $emailDtoArray;
    }
}
