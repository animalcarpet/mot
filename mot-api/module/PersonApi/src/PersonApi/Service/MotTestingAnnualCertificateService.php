<?php

namespace PersonApi\Service;

use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\QualificationAnnualCertificate;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\QualificationAnnualCertificateRepository;
use DvsaEntities\Repository\VehicleClassGroupRepository;
use PersonApi\Assertion\MotTestingAnnualCertificateAssertion;
use PersonApi\Service\Mapper\MotTestingAnnualCertificateMapper;
use PersonApi\Service\MotTestingAnnualCertificate\MotTestingAnnualCertificateEventService;
use PersonApi\Service\Validator\MotTestingAnnualCertificateValidator;

class MotTestingAnnualCertificateService implements AutoWireableInterface
{
    private $personRepository;
    private $vehicleClassGroupRepository;
    private $certificateRepository;
    private $assertion;
    private $event;
    private $validator;
    private $mapper;

    public function __construct(
        PersonRepository $personRepository,
        VehicleClassGroupRepository $vehicleClassGroupRepository,
        QualificationAnnualCertificateRepository $certificateRepository,
        MotTestingAnnualCertificateAssertion $assertion,
        MotTestingAnnualCertificateEventService $event
    ) {
        $this->personRepository = $personRepository;
        $this->vehicleClassGroupRepository = $vehicleClassGroupRepository;
        $this->certificateRepository = $certificateRepository;
        $this->assertion = $assertion;
        $this->event = $event;
        $this->validator = new MotTestingAnnualCertificateValidator();
        $this->mapper = new MotTestingAnnualCertificateMapper();
    }

    public function get($id, $personId, $group)
    {
        $person = $this->personRepository->get($personId);
        $this->assertion->assertGrantedView($person);
        $certificate = $this->certificateRepository->getOneByIdAndGroupAndPersonId($id, $personId, $group);

        return $this->mapper->toDto($certificate);
    }

    public function getListByGroup($personId, $group, $siteId = null)
    {
        $person = $this->personRepository->get($personId);
        $this->assertion->assertGrantedView($person, $siteId);
        $certificates = $this->certificateRepository->findAllByGroupAndPersonId($personId, $group);

        return $this->mapper->manyToDto($certificates);
    }

    public function create($personId, $group, MotTestingAnnualCertificateDto $dto)
    {
        $person = $this->personRepository->get($personId);
        $this->assertion->assertGrantedCreate($person);
        $this->validator->validate($dto);

        $certificate = $this->buildEntityFromDto($person, $group, $dto);
        $this->certificateRepository->save($certificate);
        $this->event->sendCreateEvent($certificate);

        $dto->setId($certificate->getId());

        return $dto;
    }

    public function update($id, $personId, $group, MotTestingAnnualCertificateDto $dto)
    {
        if ($id !== $dto->getId()) {
            ErrorSchema::throwError('Id does not match');
        }

        $person = $this->personRepository->get($personId);
        $this->assertion->assertGrantedUpdate($person);

        $this->validator->validate($dto);
        $certificate = $this->certificateRepository->getOneByIdAndGroupAndPersonId(
            $dto->getId(),
            $person->getId(),
            $group
        );

        $oldCertificate = clone $certificate;

        $certificate
            ->setCertificateNumber($dto->getCertificateNumber())
            ->setScore($dto->getScore())
            ->setDateAwarded($dto->getExamDate());

        $this->certificateRepository->save($certificate);

        $this->event->sendUpdateEvent($oldCertificate, $certificate);

        return $this->mapper->toDto($certificate);
    }

    private function buildEntityFromDto(Person $person, $group, MotTestingAnnualCertificateDto $dto)
    {
        $certificate = new QualificationAnnualCertificate();
        $certificate->setPerson($person)
            ->setCertificateNumber($dto->getCertificateNumber())
            ->setScore($dto->getScore())
            ->setDateAwarded($dto->getExamDate())
            ->setVehicleClassGroup($this->vehicleClassGroupRepository->getByCode($group));

        return $certificate;
    }

    public function delete($personId, $group, $certificateId)
    {
        $person = $this->personRepository->get($personId);
        $this->assertion->assertGrantedDelete($person);

        $certificate = $this->certificateRepository->getOneByIdAndGroupAndPersonId($certificateId, $personId, $group);
        $this->certificateRepository->remove($certificate);
        $this->certificateRepository->flush();

        $this->event->sendRemoveEvent($certificate);

        return true;
    }
}
