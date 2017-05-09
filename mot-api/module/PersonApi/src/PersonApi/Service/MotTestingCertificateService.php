<?php

namespace PersonApi\Service;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaEntities\Entity\QualificationAward;
use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaEntities\Repository\VehicleClassGroupRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\PersonRepository;
use PersonApi\Assertion\ReadMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\CreateMotTestingCertificateAssertion;
use DvsaCommon\Auth\Assertion\UpdateMotTestingCertificateAssertion;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use PersonApi\Service\MotTestingCertificate\Event\MotTestingCertificateEvent;
use PersonApi\Service\Mapper\MotTestingCertificateMapper;
use PersonApi\Service\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use PersonApi\Service\Validator\MotTestingCertificateValidator;
use DvsaCommon\Date\DateTimeHolder;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaCommon\Model\VehicleClassGroup;
use DvsaCommon\Utility\ArrayUtils;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use PersonApi\Service\MotTestingCertificate\RemoveMotTestingCertificateService;
use PersonApi\Service\MotTestingCertificate\MotTestingCertificateNotification;

class MotTestingCertificateService implements AutoWireableInterface
{
    const ERROR_CERT_EXISTS = 'Mot Testing Certificate for group %s already exists';

    private $readMotTestingCertificateAssertion;
    private $createMotTestingCertificateAssertion;
    private $updateMotTestingCertificateAssertion;
    private $motTestingCertificateValidator;
    private $identityProvider;
    private $motTestingCertificateRepository;
    private $vehicleClassGroupRepository;
    private $siteRepository;
    private $personRepository;
    private $event;
    private $testerGroupAuthorisationMapper;
    private $motTestingCertificateMapper;
    private $personalDetailsService;
    private $dateTimeHolder;
    private $authorisationForTestingMotRepository;
    private $authorisationForTestingMotStatusRepository;
    private $vehicleClassRepository;
    private $removeMotTestingCertificateService;
    private $notification;

    public function __construct(
        ReadMotTestingCertificateAssertion $readMotTestingCertificateAssertion,
        CreateMotTestingCertificateAssertion $createMotTestingCertificateAssertion,
        UpdateMotTestingCertificateAssertion $updateMotTestingCertificateAssertion,
        MotTestingCertificateValidator $motTestingCertificateValidator,
        MotIdentityProviderInterface $identityProvider,
        QualificationAwardRepository $motTestingCertificateRepository,
        VehicleClassGroupRepository $vehicleClassGroupRepository,
        SiteRepository $siteRepository,
        PersonRepository $personRepository,
        MotTestingCertificateEvent $event,
        TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
        MotTestingCertificateMapper $motTestingCertificateMapper,
        PersonalDetailsService $personalDetailsService,
        DateTimeHolder $dateTimeHolder,
        AuthorisationForTestingMotRepository $authorisationForTestingMotRepository,
        AuthorisationForTestingMotStatusRepository $authorisationForTestingMotStatusRepository,
        VehicleClassRepository $vehicleClassRepository,
        RemoveMotTestingCertificateService $removeMotTestingCertificateService,
        MotTestingCertificateNotification $notification
    ) {
        $this->readMotTestingCertificateAssertion = $readMotTestingCertificateAssertion;
        $this->createMotTestingCertificateAssertion = $createMotTestingCertificateAssertion;
        $this->updateMotTestingCertificateAssertion = $updateMotTestingCertificateAssertion;
        $this->motTestingCertificateValidator = $motTestingCertificateValidator;
        $this->identityProvider = $identityProvider;
        $this->motTestingCertificateRepository = $motTestingCertificateRepository;
        $this->vehicleClassGroupRepository = $vehicleClassGroupRepository;
        $this->siteRepository = $siteRepository;
        $this->personRepository = $personRepository;
        $this->event = $event;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->motTestingCertificateMapper = $motTestingCertificateMapper;
        $this->personalDetailsService = $personalDetailsService;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->authorisationForTestingMotRepository = $authorisationForTestingMotRepository;
        $this->authorisationForTestingMotStatusRepository = $authorisationForTestingMotStatusRepository;
        $this->vehicleClassRepository = $vehicleClassRepository;
        $this->removeMotTestingCertificateService = $removeMotTestingCertificateService;
        $this->notification = $notification;
    }

    /**
     * @param int $personId
     *
     * @return MotTestingCertificateDto[]
     */
    public function getList($personId)
    {
        $person = $this->personRepository->get($personId);

        $this->readMotTestingCertificateAssertion->assertGranted($person, $this->getPersonSystemRoles($personId));

        $certificates = $this->motTestingCertificateRepository->findAllByPersonId($personId);

        return $this->motTestingCertificateMapper->manyToDto($certificates);
    }

    /**
     * @param string $group
     * @param int    $personId
     *
     * @return MotTestingCertificateDto
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function get($group, $personId)
    {
        $person = $this->personRepository->get($personId);

        $this->readMotTestingCertificateAssertion->assertGranted($person, $this->getPersonSystemRoles($personId));

        $certificate = $this->motTestingCertificateRepository->getOneByGroupAndPersonId($group, $personId);

        return $this->motTestingCertificateMapper->toDto($certificate);
    }

    public function create($personId, MotTestingCertificateDto $dto)
    {
        $authorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($personId);
        $personDetails = $this->personalDetailsService->get($personId);
        $personSystemRoles = $personDetails->getRoles()['system'];

        $this->createMotTestingCertificateAssertion->assertGranted($personId, $dto->getVehicleClassGroupCode(), $personSystemRoles, $authorisation);

        $this->validate($dto);

        $cert = $this->motTestingCertificateRepository->findOneByGroupAndPersonId($dto->getVehicleClassGroupCode(), $personId);
        if ($cert !== null) {
            throw new \InvalidArgumentException(sprintf(self::ERROR_CERT_EXISTS, $dto->getVehicleClassGroupCode()));
        }

        $person = $this->personRepository->get($personId);
        $motTestingCertificate = new QualificationAward();
        $motTestingCertificate->setPerson($person);

        $motTestingCertificate = $this->save($motTestingCertificate, $dto);
        $this->changeQualificationStatus($person, $dto->getVehicleClassGroupCode());

        $this->event->sendCreateEvent($motTestingCertificate);
        $this->notification->sendCreateNotification($motTestingCertificate);

        return $this->motTestingCertificateMapper->toDto($motTestingCertificate);
    }

    public function update($group, $personId, MotTestingCertificateDto $dto)
    {
        $authorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($personId);
        $personDetails = $this->personalDetailsService->get($personId);
        $personSystemRoles = $personDetails->getRoles()['system'];

        $this->updateMotTestingCertificateAssertion->assertGranted($personId, $group, $personSystemRoles, $authorisation);

        if ($group !== $dto->getVehicleClassGroupCode()) {
            throw new \InvalidArgumentException();
        }

        $this->validate($dto);

        $motTestingCertificate = $this->motTestingCertificateRepository->getOneByGroupAndPersonId(
            $group,
            $personId
        );

        $motTestingCertificate = $this->save($motTestingCertificate, $dto);

        $this->event->sendUpdateEvent($motTestingCertificate);

        return $this->motTestingCertificateMapper->toDto($motTestingCertificate);
    }

    public function remove($personId, $group)
    {
        $this->removeMotTestingCertificateService->execute($personId, $group);
    }

    private function save(QualificationAward $motTestingCertificate, MotTestingCertificateDto $dto)
    {
        $vehicleClassGroup = $this->vehicleClassGroupRepository->getByCode($dto->getVehicleClassGroupCode());

        $siteNumber = $dto->getSiteNumber();
        $site = null;

        if (!empty($siteNumber)) {
            $site = $this->siteRepository->getBySiteNumber($siteNumber);
        }

        $motTestingCertificate
            ->setSite($site)
            ->setVehicleClassGroup($vehicleClassGroup)
            ->setCertificateNumber($dto->getCertificateNumber())
            ->setDateOfQualification(new \DateTime($dto->getDateOfQualification()))
        ;

        $this->motTestingCertificateRepository->save($motTestingCertificate);

        return $motTestingCertificate;
    }

    public function validate(MotTestingCertificateDto $dto)
    {
        $this->motTestingCertificateValidator->validate($dto);
    }

    /**
     * @param $personId
     *
     * @return []
     */
    private function getPersonSystemRoles($personId)
    {
        $personDetails = $this->personalDetailsService->get($personId);

        return $personDetails->getRoles()['system'];
    }

    /**
     * @param Person $person
     */
    private function changeQualificationStatus(Person $person, $vehicleClassGroup)
    {
        $authorisations = $this->getAuthorisationsForGroup($person, $vehicleClassGroup);
        $demoTestNeededStatus = $this->getDemoTestNeededStatus();

        if (!empty($authorisations)) {
            foreach ($authorisations as $authorisation) {
                $authorisation->setStatus($demoTestNeededStatus);
                $this->authorisationForTestingMotRepository->persist($authorisation);
            }
        } else {
            foreach (VehicleClassGroup::getClassesForGroup($vehicleClassGroup) as $code) {
                $vehicleClass = $this->vehicleClassRepository->getByCode($code);
                $authorisation = new AuthorisationForTestingMot();
                $authorisation
                    ->setPerson($person)
                    ->setStatus($demoTestNeededStatus)
                    ->setVehicleClass($vehicleClass);

                $this->authorisationForTestingMotRepository->persist($authorisation);
            }
        }

        $this->authorisationForTestingMotRepository->flush();
    }

    private function getAuthorisationsForGroup(Person $person, $group)
    {
        $classesInGroup = VehicleClassGroup::getClassesForGroup($group);

        /** @var AuthorisationForTestingMot[] $authorisations */
        $authorisations = ArrayUtils::filter(
            $person->getAuthorisationsForTestingMot(),
            function (AuthorisationForTestingMot $authorisationForTestingMot) use ($classesInGroup) {
                return in_array($authorisationForTestingMot->getVehicleClass(), $classesInGroup);
            }
        );

        return $authorisations;
    }

    private function getDemoTestNeededStatus()
    {
        return $this->authorisationForTestingMotStatusRepository->getByCode(AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED);
    }
}
