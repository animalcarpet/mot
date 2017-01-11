<?php

namespace UserApi\Dashboard\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer as AuthorisationForAuthorisedExaminerEntity;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaMotApi\Service\TesterService;
use NotificationApi\Service\NotificationService;
use SiteApi\Service\SiteService;
use UserApi\Dashboard\Dto\AuthorisationForAuthorisedExaminer;
use UserApi\Dashboard\Dto\DashboardData;
use UserApi\Person\Service\PersonalAuthorisationForMotTestingService;
use UserApi\SpecialNotice\Service\SpecialNoticeService;
use UserFacade\UserFacadeInterface;

/**
 * Data for dashboard
 */
class DashboardService extends AbstractService
{

    /** @var $siteService SiteService */
    private $siteService;
    /** @var UserFacadeInterface $userFacade */
    private $userFacade;
    /** @var $specialNoticeService SpecialNoticeService */
    private $specialNoticeService;
    /** @var $notificationService NotificationService */
    private $notificationService;
    /** @var $personalAuthorisationService PersonalAuthorisationForMotTestingService */
    private $personalAuthorisationService;
    /** @var $testerService TesterService */
    private $testerService;
    /** @var  $authorisationService AuthorisationServiceInterface */
    protected $authorisationService;
    /** @var  $authForAeRepository EntityRepository */
    protected $authForAeRepository;

    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authorisationService,
        UserFacadeInterface $userFacade,
        SiteService $siteService,
        SpecialNoticeService $specialNoticeService,
        NotificationService $notificationService,
        PersonalAuthorisationForMotTestingService $personalAuthorisationService,
        TesterService $testerService,
        EntityRepository $authForAeRepository
    ) {
        parent::__construct($entityManager);

        $this->authorisationService = $authorisationService;
        $this->siteService = $siteService;
        $this->userFacade = $userFacade;
        $this->specialNoticeService = $specialNoticeService;
        $this->notificationService = $notificationService;
        $this->personalAuthorisationService = $personalAuthorisationService;
        $this->testerService = $testerService;
        $this->authForAeRepository = $authForAeRepository;
    }

    /**
     * @param int $personId
     *
     * @return DashboardData
     */
    public function getDataForDashboardByPersonId($personId)
    {
        /** @var $person Person */
        $person = $this->findOrThrowException(Person::class, $personId, Person::ENTITY_NAME);

        $dtoAeList = $this->getAuthorisedExaminersByPerson($person);
        $specialNotice = $this->specialNoticeService->specialNoticeSummaryForUser($person->getUsername());
        $notifications = $this->notificationService->getAllByPersonId($personId);
        $vtcAuthorisations = $this->personalAuthorisationService->getPersonalTestingAuthorisation($personId);
        $inProgressTestId = $this->testerService->findInProgressTestIdForTester($personId);
        $isTesterQualified = $person->isQualifiedTester();
        $isTesterActive = $this->testerService->isTesterActiveByUser($person);

        $dashboard = new DashboardData(
            [],
            $dtoAeList,
            $specialNotice,
            $notifications,
            $vtcAuthorisations->toArray(),
            $inProgressTestId,
            $isTesterQualified,
            $isTesterActive,
            $this->authorisationService
        );

        return $dashboard;
    }

    /**
     * @param Person $person
     *
     * @return SiteBusinessRoleMap[]
     */
    private function getPositionAtSites(Person $person, BusinessRoleStatus $status)
    {
        $entityRepository = $this->entityManager->getRepository(SiteBusinessRoleMap::class);

        return $entityRepository->findBy(
            ['person' => $person, 'businessRoleStatus' => $status]
        );
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity $ae
     *
     * @return Site[]
     */
    private function getSitesByAe(AuthorisationForAuthorisedExaminerEntity $ae)
    {
        $entityRepository = $this->entityManager->getRepository(Site::class);
        $entities = $entityRepository->findBy(['organisation' => $ae->getOrganisation()]);

        return $entities;
    }

    /**
     * @param Person $person
     *
     * @return AuthorisationForAuthorisedExaminer[]
     */
    public function getAuthorisedExaminersByPerson(Person $person)
    {
        $aedmRole = $this->entityManager->getRepository(Entity\OrganisationBusinessRole::class)->findOneBy(
            ['name' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]
        );
        $aedRole = $this->entityManager->getRepository(Entity\OrganisationBusinessRole::class)->findOneBy(
            ['name' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]
        );
        $status = $this->entityManager->getRepository(\DvsaEntities\Entity\BusinessRoleStatus::class)->findOneBy(
            ['code' => 'AC']
        );

        /** @var \DvsaEntities\Repository\OrganisationRepository $organisationRepository */
        $organisationRepository = $this->entityManager->getRepository(\DvsaEntities\Entity\Organisation::class);

        $organisationsForDesignatedManager = $organisationRepository->findForPersonWithRole($person, $aedmRole, $status);
        $organisationsForDelegate = $organisationRepository->findForPersonWithRole($person, $aedRole, $status);
        $positionAtSites = $this->getPositionAtSites($person, $status);
        $aesForDesignatedManager = $this->getAesForOrganisations($organisationsForDesignatedManager);
        $aesForDelegate = $this->getAesForOrganisations($organisationsForDelegate);
        $aesForSitePosition = $this->authForAeRepository->getBySitePositionForPerson($person);

        $allUniqueAesById = $this->getUniqueAesById(
            array_merge(
                $aesForDesignatedManager,
                $aesForDelegate,
                $aesForSitePosition
            )
        );
        $aesPositionNames = $this->getAesPositionNames($allUniqueAesById, $aesForDesignatedManager, $aesForDelegate, $aedmRole->getFullName(), $aedRole->getFullName());

        $positionsBySite = $this->getPositionsBySite($positionAtSites);
        $sitesByAe = $this->getSitesByAes($allUniqueAesById);
        return $this->getAesWithSitesAndPositions($allUniqueAesById, $person, $sitesByAe, $positionsBySite, $aesPositionNames);
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesById
     * @return Site[][]
     */
    private function getSitesByAes($aesById) {
        return ArrayUtils::map($aesById, function(AuthorisationForAuthorisedExaminerEntity $authorisedExaminer) {
            return $this->getSitesByAe($authorisedExaminer);
        });
    }

    /**
     * @param Organisation[] $organisations
     * @return AuthorisationForAuthorisedExaminerEntity[]
     */
    private function getAesForOrganisations(array $organisations) {
        return ArrayUtils::map($organisations, function(Organisation $organisation) {
            return $organisation->getAuthorisedExaminer();
        });
    }

    /**
     * @param SiteBusinessRoleMap[] $siteRoles
     * @return AuthorisationForAuthorisedExaminerEntity[]
     */
    private function getAesForSitePositions(array $siteRoles) {
        return ArrayUtils::map($siteRoles, function(SiteBusinessRoleMap $position) {
            return $position->getSite()->getAuthorisedExaminer();
        });
    }

    /**
     * @param SiteBusinessRoleMap[] $positionAtSites
     * @return SiteBusinessRoleMap[][] map from site ID to array of SiteBusinessRoleMap
     */
    private function getPositionsBySite(array $positionAtSites) {
        return ArrayUtils::groupBy($positionAtSites, function(SiteBusinessRoleMap $position) {
            return $position->getSite()->getId();
        });
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aes
     * @return int[]
     */
    private function getAesIdsForAes(array $aes) {
        return ArrayUtils::map($aes, function(AuthorisationForAuthorisedExaminerEntity $ae) {
            return $ae->getId();
        });
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aes
     * @return AuthorisationForAuthorisedExaminerEntity[] map from AE ID to AE
     */
    private function getUniqueAesById(array $aes) {
        $groupedAes = ArrayUtils::groupBy($aes, function(AuthorisationForAuthorisedExaminerEntity $ae) {
            return $ae->getId();
        });
        return ArrayUtils::map($groupedAes, function(array $aes) {
            return $aes[0];
        });
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesById
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesForDesignatedManager
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesForDelegate
     * @param string $aedmRoleName
     * @param string $aedRoleName
     * @return string[]
     */
    private function getAesPositionNames($aesById, $aesForDesignatedManager, $aesForDelegate, $aedmRoleName, $aedRoleName) {
        $aesForDesignatedManagerIds = $this->getAesIdsForAes($aesForDesignatedManager);
        $aesForDelegateIds = $this->getAesIdsForAes($aesForDelegate);

        return ArrayUtils::map($aesById, function(AuthorisationForAuthorisedExaminerEntity $ae) use ($aesForDesignatedManagerIds, $aesForDelegateIds, $aedmRoleName, $aedRoleName) {
            $orgId = $ae->getId();
            if (in_array($orgId, $aesForDesignatedManagerIds)) {

                return $aedmRoleName;
            } elseif (in_array($orgId, $aesForDelegateIds)) {

                return $aedRoleName;
            } else {

                return '';
            }
        });
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesById
     * @param int $personId
     * @param Site[][] $sitesByAe
     * @param SiteBusinessRoleMap[][] $positionsBySite
     * @param string[] $aesPositionNames
     * @return AuthorisationForAuthorisedExaminer[]
     */
    public function getAesWithSitesAndPositions($aesById, $personId, $sitesByAe, $positionsBySite, $aesPositionNames) {
        return ArrayUtils::map($aesById, function(AuthorisationForAuthorisedExaminerEntity $authorisedExaminer) use ($personId, $sitesByAe, $positionsBySite, $aesPositionNames) {
            $designatedManager = $authorisedExaminer->getDesignatedManager();
            $sitesWithPositions = ArrayUtils::map($sitesByAe[$authorisedExaminer->getId()], function(Site $site) use ($positionsBySite) {

                return new \UserApi\Dashboard\Dto\Site($site, ArrayUtils::tryGet($positionsBySite, $site->getId(), []));
            });

            return new AuthorisationForAuthorisedExaminer(
                $authorisedExaminer,
                ($designatedManager ? $designatedManager->getId() : null),
                $sitesWithPositions,
                $aesPositionNames[$authorisedExaminer->getId()],
                $personId
            );
        });
    }
}
