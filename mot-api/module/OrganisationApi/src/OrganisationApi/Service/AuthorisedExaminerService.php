<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Dto\Organisation\OrganisationContactDto;
use DvsaCommon\Dto\Organisation\OrganisationDto;
use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Filter\XssFilter;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\AuthForAeStatusRepository;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use DvsaEntities\Repository\CompanyTypeRepository;
use DvsaEntities\Repository\OrganisationContactTypeRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\OrganisationTypeRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEventApi\Service\EventService;
use OrganisationApi\Service\Mapper\OrganisationMapper;
use OrganisationApi\Service\Validator\AuthorisedExaminerValidator;

/**
 * Service to deal with creating, editing and viewing AuthorisedExaminers.
 */
class AuthorisedExaminerService extends AbstractService
{
    const FIELD_CORRESPONDENCE_CONTACT_DETAILS_SAME = 'isCorrespondenceContactDetailsSame';
    const FIELD_AREA_OFFICE_NUMBER = 'areaOfficeNumber';

    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;
    /**
     * @var MotIdentityInterface
     */
    private $identity;
    /**
     * @var ContactDetailsService
     */
    private $contactDetailService;
    /**
     * @var EventService
     */
    private $eventService;
    /**
     * @var OrganisationRepository
     */
    private $organisationRepository;
    /**
     * @var PersonRepository
     */
    private $personRepository;
    /**
     * @var OrganisationTypeRepository
     */
    private $organisationTypeRepository;
    /**
     * @var SiteRepository
     */
    private $siteRepository;
    /**
     * @var CompanyTypeRepository
     */
    private $companyTypeRepository;
    /**
     * @var OrganisationContactTypeRepository
     */
    private $organisationContactTypeRepository;
    /**
     * @var OrganisationMapper
     */
    private $mapper;
    /**
     * @var \DvsaCommonApi\Filter\XssFilter
     */
    protected $xssFilter;
    /**
     * @var AuthForAeStatusRepository
     */
    private $authForAeStatusRepository;
    /**
     * @var AuthorisationForAuthorisedExaminerRepository
     */
    private $authForAeRepository;
    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;
    /**
     * @var AuthorisedExaminerValidator
     */
    private $validator;

    /**
     * @param EntityManager                                $entityManager
     * @param AuthorisationServiceInterface                $authService
     * @param MotIdentityInterface                         $motIdentity
     * @param ContactDetailsService                        $contactDetailService
     * @param EventService                                 $eventService
     * @param OrganisationRepository                       $organisationRepository
     * @param PersonRepository                             $personRepository
     * @param OrganisationTypeRepository                   $organisationTypeRepository
     * @param CompanyTypeRepository                        $companyTypeRepository
     * @param OrganisationContactTypeRepository            $organisationContactTypeRepository
     * @param OrganisationMapper                           $mapper
     * @param AuthForAeStatusRepository                    $authForAeStatusRepository
     * @param XssFilter                                    $xssFilter
     * @param AuthorisationForAuthorisedExaminerRepository $authorisationForAuthorisedExaminerRepository
     * @param AuthorisedExaminerValidator                  $validator
     * @param DateTimeHolder                               $dateTimeHolder
     */
    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authService,
        MotIdentityInterface $motIdentity,
        ContactDetailsService $contactDetailService,
        EventService $eventService,
        OrganisationRepository $organisationRepository,
        PersonRepository $personRepository,
        OrganisationTypeRepository $organisationTypeRepository,
        CompanyTypeRepository $companyTypeRepository,
        OrganisationContactTypeRepository $organisationContactTypeRepository,
        OrganisationMapper $mapper,
        AuthForAeStatusRepository $authForAeStatusRepository,
        XssFilter $xssFilter,
        AuthorisationForAuthorisedExaminerRepository $authorisationForAuthorisedExaminerRepository,
        AuthorisedExaminerValidator $validator,
        DateTimeHolder $dateTimeHolder,
        SiteRepository $siteRepository
    ) {
        parent::__construct($entityManager);

        $this->authService = $authService;
        $this->identity = $motIdentity;
        $this->contactDetailService = $contactDetailService;
        $this->eventService = $eventService;
        $this->organisationRepository = $organisationRepository;
        $this->personRepository = $personRepository;
        $this->organisationTypeRepository = $organisationTypeRepository;
        $this->companyTypeRepository = $companyTypeRepository;
        $this->organisationContactTypeRepository = $organisationContactTypeRepository;
        $this->siteRepository = $siteRepository;

        $this->mapper = $mapper;
        $this->authForAeStatusRepository = $authForAeStatusRepository;
        $this->xssFilter = $xssFilter;
        $this->authForAeRepository = $authorisationForAuthorisedExaminerRepository;
        $this->validator = $validator;

        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * @param OrganisationDto $orgDto
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function create(OrganisationDto $orgDto)
    {
        $this->authService->assertGranted(PermissionInSystem::AUTHORISED_EXAMINER_CREATE);

        /** @var OrganisationDto $orgDto */
        $orgDto = $this->xssFilter->filter($orgDto);

        $validAreaOffices = $this->siteRepository->getAllAreaOffices();
        $this->validator->validate($orgDto, $validAreaOffices);

        if ($orgDto->isValidateOnly() === true) {
            return null;
        }

        $orgEntity = new Organisation();
        $this->populateOrganisationFromDto($orgDto, $orgEntity);

        //  logical block :: create contact
        /** @var OrganisationContactDto $contactDto */
        foreach ($orgDto->getContacts() as $contactDto) {
            /** @var OrganisationContactType $contactType */
            $contactType = $this->organisationContactTypeRepository->getByCode($contactDto->getType());

            $contactDetails = $this->contactDetailService->setContactDetailsFromDto(
                $contactDto, new ContactDetail()
            );

            $orgEntity->setContact($contactDetails, $contactType);
        }

        //  logical block :: create authorisation for AE
        $aeNumber = $this->authForAeRepository->getNextAeRef();

        /** @var AuthForAeStatus $status */
        $status = $this->authForAeStatusRepository->getByCode(AuthorisationForAuthorisedExaminerStatusCode::APPLIED);

        // Associate the supplied Area Office to the Organisation
        $authForAe = $orgDto->getAuthorisedExaminerAuthorisation();
        $addedAO = false;

        if ($authForAe) {
            $aoNumber = $authForAe->getAssignedAreaOffice();
            $newAOId = $this->getAreaOfficeIdByNumber($aoNumber);
            $selectedAO = $this->siteRepository->find($newAOId);

            if ($selectedAO) {
                $authorisedExaminer = new AuthorisationForAuthorisedExaminer();
                $authorisedExaminer
                    ->setValidFrom(new \DateTime())
                    ->setStatus($status)
                    ->setOrganisation($orgEntity)
                    ->setNumber($aeNumber)
                    ->setAreaOffice($selectedAO);
                $addedAO = true;
            }
        }

        // Ensure the Area Office wiring was installed OK
        if (false === $addedAO) {
            throw new NotFoundException('Areao Office Site', $aoNumber);
        }

        //  logical block :: create event
        $event = $this->eventService->addEvent(
            EventTypeCode::DVSA_ADMINISTRATOR_CREATE_AE,
            sprintf(
                EventDescription::DVSA_ADMINISTRATOR_CREATE_AE,
                $aeNumber,
                $orgDto->getName(),
                $this->getUserName()
            ),
            $this->dateTimeHolder->getCurrent(true)
        );

        $eventMap = (new EventOrganisationMap())
            ->setEvent($event)
            ->setOrganisation($orgEntity);

        //  logical block :: store in db
        $this->entityManager->persist($orgEntity);
        $this->entityManager->persist($authorisedExaminer);
        $this->entityManager->persist($eventMap);
        $this->entityManager->flush();

        return [
            'id' => $orgEntity->getId(),
            'aeRef' => $authorisedExaminer->getNumber(),
        ];
    }

    /**
     * @param OrganisationDto $dto
     * @param Organisation    $orgEntity
     *
     * @return array [ Organisation entity, Site entity (Area Office) ]
     *
     * @throws NotFoundException
     */
    private function populateOrganisationFromDto(OrganisationDto $dto, Organisation $orgEntity)
    {
        $val = $dto->getName();
        if ($val !== null) {
            $orgEntity->setName($val);
        }

        $val = $dto->getRegisteredCompanyNumber();
        if ($val !== null) {
            $orgEntity->setRegisteredCompanyNumber(strtoupper($val));
        }

        $val = $dto->getTradingAs();
        if ($val !== null) {
            $orgEntity->setTradingAs($val);
        }

        $companyType = $dto->getCompanyType();
        if ($companyType !== null) {
            $typeEntity = $this->companyTypeRepository->getByCode($companyType);

            $orgEntity->setCompanyType($typeEntity);
        }

        return $orgEntity;
    }

    private function getAreaOfficeIdByNumber($aoNumber)
    {
        $allAreaOffices = $this->siteRepository->getAllAreaOffices();
        $aoNumber = (int) $aoNumber;

        foreach ($allAreaOffices as $areaOffice) {
            if ($aoNumber == $areaOffice['areaOfficeNumber']) {
                return $areaOffice['id'];
            }
        }

        return null;
    }

    /**
     * @param Organisation $org
     * @param array        $data
     *
     * @return Organisation
     */
    public function hydrateOrganisation(Organisation $org, $data)
    {
        $companyType = ArrayUtils::tryGet($data, 'companyType');
        if ($companyType) {
            $type = $this->companyTypeRepository->findOneByName($companyType);
            $org->setCompanyType($type);
        }

        $organisationType = ArrayUtils::tryGet($data, 'organisationType');
        if ($organisationType) {
            $type = $this->organisationTypeRepository->findOneByName($organisationType);
            $org->setOrganisationType($type);
        }

        $org->setName(ArrayUtils::tryGet($data, 'organisationName'));
        $org->setTradingAs(ArrayUtils::tryGet($data, 'tradingAs'));
        $org->setRegisteredCompanyNumber(ArrayUtils::tryGet($data, 'registeredCompanyNumber'));

        return $org;
    }

    /**
     * @param $id
     *
     * @return OrganisationDto
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        if (!$this->authService->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_READ_FULL)) {
            $this->authService->assertGrantedAtOrganisation(PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $id);
        }

        $organisation = $this->organisationRepository->getAuthorisedExaminer($id);

        $data = $this->mapper->toDto($organisation);

        return $data;
    }

    /**
     * @param string $aeNumber Organisation the identifying number code
     *
     * @return OrganisationDto
     *
     * @throws NotFoundException
     */
    public function getByNumber($aeNumber)
    {
        $this->authService->assertGranted(PermissionInSystem::AUTHORISED_EXAMINER_LIST);

        $data = null;

        $data = $this->entityManager
            ->getRepository(AuthorisationForAuthorisedExaminer::class)
            ->findOneBy(['number' => $aeNumber]);

        if (is_null($data)) {
            throw new NotFoundException('Organisation ID: '.$aeNumber);
        }

        return $this->mapper->toDto($data->getOrganisation());
    }

    /**
     * @param $personId
     *
     * @return OrganisationDto[]
     */
    public function getAuthorisedExaminersForPerson($personId)
    {
        $this->authService->assertGranted(PermissionInSystem::AUTHORISED_EXAMINER_LIST);

        /** @var Person $manager */
        $manager = $this->personRepository->get($personId);
        $organisations = $manager->findAuthorisedExaminers();

        foreach ($organisations as $org) {
            $this->authService->assertGrantedAtOrganisation(
                PermissionAtOrganisation::AUTHORISED_EXAMINER_READ,
                $org->getId()
            );
        }

        $data = $this->mapper->manyToDto($organisations);

        return $data;
    }

    /**
     * @param $username
     *
     * @return mixed
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @deprecated Should be removed at all cost
     */
    public function getAuthorisedExaminerData($username)
    {
        $aeData = $this->authForAeRepository->getAuthorisedExaminerData($username);

        if (empty($aeData)) {
            throw new NotFoundException('AuthorisationForAuthorisedExaminer', $username);
        }

        // Don't bother hydrating, just return array for direct serialization.
        return $aeData;
    }

    /**
     * @return string
     * @description return Username
     */
    private function getUserName()
    {
        return $this->identity->getUsername();
    }
}
