<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Constants\PersonContactType;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Mapper\PersonMapper;
use PersonApi\Service\Validator\BasePersonValidator;
use DvsaCommonApi\Filter\XssFilter;

/**
 * Service to create bare minimum Person entities.
 */
class BasePersonService
{
    protected $entityManager;
    protected $validator;
    protected $contactDetailsService;
    protected $personMapper;

    /**
     * @param \Doctrine\ORM\EntityManager                      $entityManager
     * @param \PersonApi\Service\Validator\BasePersonValidator $validator
     * @param \DvsaCommonApi\Service\ContactDetailsService     $contactDetailsService
     * @param \DvsaEntities\Mapper\PersonMapper                $personMapper
     * @param \DvsaCommonApi\Filter\XssFilter                  $xssFilter
     */
    public function __construct(
        EntityManager $entityManager,
        BasePersonValidator $validator,
        ContactDetailsService $contactDetailsService,
        PersonMapper $personMapper,
        XssFilter $xssFilter
    ) {
        $this->entityManager = $entityManager;
        $this->validator = $validator;
        $this->contactDetailsService = $contactDetailsService;
        $this->personMapper = $personMapper;
        $this->xssFilter = $xssFilter;
    }

    /**
     * @param $data
     *
     * @return \DvsaEntities\Entity\Person
     *
     * @throws \Exception
     */
    public function create($data)
    {
        // Strip script tags from form data (avoid XSS vulns)
        $data = $this->xssFilter->filterMultiple($data);

        $this->validator->validate($data);

        $em = $this->entityManager;
        $em->getConnection()->beginTransaction();

        try {
            $person = $this->createPerson($data);
            $this->persistAndFlush($person);

            $this->createAndPersistContactDetails($data, $person);

            $em->getConnection()->commit();

            return $person;
        } catch (\Exception $e) {
            $em->getConnection()->rollback();
            throw $e;
        }
    }

    protected function createPerson($data)
    {
        $person = $this->personMapper->mapToObject(new Person(), $data);

        return $person;
    }

    protected function createAndPersistContactDetails($data, $person)
    {
        $contact = $this->contactDetailsService->create($data, PhoneContactTypeCode::PERSONAL, true);
        $this->createPersonContact($person, $contact);
    }

    protected function createPersonContact(Person $person, ContactDetail $contact)
    {
        $personContactTypeRepository = $this->entityManager->getRepository(\DvsaEntities\Entity\PersonContactType::class);
        $personContactType = $personContactTypeRepository->findOneBy(['name' => PersonContactType::PERSONAL]);
        $personContact = new PersonContact($contact, $personContactType, $person);
        $this->persistAndFlush($personContact);

        return $personContact;
    }

    protected function persistAndFlush($dataToPersist)
    {
        $this->entityManager->persist($dataToPersist);
        $this->entityManager->flush();
    }
}
