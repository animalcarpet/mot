<?php

namespace DvsaEntities\Mapper;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\GenderRepository;
use DvsaEntities\Repository\TitleRepository;

/**
 * Person details mapper.
 */
class PersonMapper
{
    private $titleRepository;
    private $genderRepository;

    public function __construct(
        TitleRepository $titleRepository,
        GenderRepository $genderRepository
    ) {
        $this->titleRepository = $titleRepository;
        $this->genderRepository = $genderRepository;
    }

    /**
     * Map data sent to MOT-API.
     *
     * @param Person $person
     * @param array  $data
     *
     * @return Person
     */
    public function mapToObject(Person $person, array $data)
    {
        $person
            ->setFirstName(ArrayUtils::tryGet($data, 'firstName', ''))
            ->setMiddleName(ArrayUtils::tryGet($data, 'middleName', ''))
            ->setFamilyName(ArrayUtils::tryGet($data, 'surname', ''));

        if (isset($data['title'])) {
            $person->setTitle($this->titleRepository->getByName($data['title']));
        }
        if (isset($data['dateOfBirth'])) {
            $person->setDateOfBirth(DateUtils::toDate($data['dateOfBirth']));
        }
        if (isset($data['gender'])) {
            $person->setGender($this->genderRepository->getByName($data['gender']));
        }

        return $person;
    }
}
