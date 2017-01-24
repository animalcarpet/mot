<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer as AuthorisationForAuthorisedExaminerEntity;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\AuthorisationForAuthorisedExaminerRepository;
use DvsaMotApi\Service\TesterService;
use NotificationApi\Service\NotificationService;
use SiteApi\Service\SiteService;
use UserApi\Dashboard\Dto\AuthorisationForAuthorisedExaminer;
use UserApi\Dashboard\Dto\DashboardData;
use UserApi\SpecialNotice\Service\SpecialNoticeService;

/**
 * Data for dashboard
 */
class DashboardService extends AbstractService
{
    /** @var $siteService SiteService */
    private $siteService;
    /** @var $specialNoticeService SpecialNoticeService */
    private $specialNoticeService;
    /** @var $notificationService NotificationService */
    private $notificationService;
    /** @var $personalAuthorisationService PersonalAuthorisationForMotTestingService */
    private $personalAuthorisationService;
    /** @var $testerService TesterService */
    private $testerService;
    /** @var  $authorisationService AuthorisationServiceInterface */
    private $authorisationService;
    /** @var $authForAeRepository AuthorisationForAuthorisedExaminerRepository */
    private $authForAeRepository;

    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authorisationService,
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
        $overdueSpecialNotice = $this->specialNoticeService->getAmountOfOverdueSpecialNoticesForClasses();
        $notifications = $this->notificationService->getUnreadByPersonId($personId, 5);
        $unreadNotificationsCount = $this->notificationService->countUnreadByPersonId($personId);
        $inProgressTest = $this->testerService->findInProgressTestForTester($personId);
        $inProgressDemoTestNumber = $this->testerService->findInProgressDemoTestNumberForTester($personId);
        $inProgressNonMotTestNumber = null;
        $isTesterQualified = $person->isQualifiedTester();
        $isTesterActive = $this->testerService->isTesterActiveByUser($person);

        if ($this->authorisationService->isGranted(PermissionInSystem::ENFORCEMENT_NON_MOT_TEST_PERFORM)) {
            $inProgressNonMotTestNumber = $this->testerService->findInProgressNonMotTestNumberForVehicleExaminer($personId);
        }

        $dashboard = new DashboardData(
            $dtoAeList,
            $specialNotice,
            $overdueSpecialNotice,
            $notifications,
            $unreadNotificationsCount,
            $inProgressTest !== null ? $inProgressTest->getNumber() : null,
            $inProgressDemoTestNumber,
            $inProgressNonMotTestNumber,
            $isTesterQualified,
            $isTesterActive,
            $inProgressTest !== null ? $inProgressTest->getMotTestType()->getCode() : null,
            $this->authorisationService
        );

        return $dashboard;
    }

    /**
     * @param Person             $person
     * @param BusinessRoleStatus $status
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
        /** @var Entity\OrganisationBusinessRole $aedmRole */
        $aedmRole = $this->entityManager->getRepository(Entity\OrganisationBusinessRole::class)->findOneBy(
            ['name' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER]
        );
        /** @var Entity\OrganisationBusinessRole $aedRole */
        $aedRole = $this->entityManager->getRepository(Entity\OrganisationBusinessRole::class)->findOneBy(
            ['name' => OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE]
        );
        /** @var BusinessRoleStatus $status */
        $status = $this->entityManager->getRepository(BusinessRoleStatus::class)->findOneBy(
            ['code' => BusinessRoleStatusCode::ACTIVE]
        );

        /** @var \DvsaEntities\Repository\OrganisationRepository $organisationRepository */
        $organisationRepository = $this->entityManager->getRepository(Organisation::class);

        $organisationsForDesignatedManager = $organisationRepository->findForPersonWithRole(
            $person,
            $aedmRole,
            $status
        );
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
        $aesPositionNames = $this->getAesPositionNames(
            $allUniqueAesById,
            $aesForDesignatedManager,
            $aesForDelegate,
            $aedmRole->getFullName(),
            $aedRole->getFullName()
        );

        $positionsBySite = $this->getPositionsBySite($positionAtSites);
        $sitesByAe = $this->getSitesByAes($allUniqueAesById);

        return $this->getAesWithSitesAndPositions(
            $allUniqueAesById,
            $person->getId(),
            $sitesByAe,
            $positionsBySite,
            $aesPositionNames
        );
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesById
     *
     * @return Site[][]
     */
    private function getSitesByAes($aesById)
    {
        return ArrayUtils::map(
            $aesById,
            function (AuthorisationForAuthorisedExaminerEntity $authorisedExaminer) {
                return $this->getSitesByAe($authorisedExaminer);
            }
        );
    }

    /**
     * @param Organisation[] $organisations
     *
     * @return AuthorisationForAuthorisedExaminerEntity[]
     */
    private function getAesForOrganisations(array $organisations)
    {
        return ArrayUtils::map(
            $organisations,
            function (Organisation $organisation) {
                return $organisation->getAuthorisedExaminer();
            }
        );
    }

    /**
     * @param SiteBusinessRoleMap[] $positionAtSites
     *
     * @return SiteBusinessRoleMap[][] map from site ID to array of SiteBusinessRoleMap
     */
    private function getPositionsBySite(array $positionAtSites)
    {
        return ArrayUtils::groupBy(
            $positionAtSites,
            function (SiteBusinessRoleMap $position) {
                return $position->getSite()->getId();
            }
        );
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aes
     *
     * @return int[]
     */
    private function getAesIdsForAes(array $aes)
    {
        return ArrayUtils::map(
            $aes,
            function (AuthorisationForAuthorisedExaminerEntity $ae) {
                return $ae->getId();
            }
        );
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aes
     *
     * @return AuthorisationForAuthorisedExaminerEntity[] map from AE ID to AE
     */
    private function getUniqueAesById(array $aes)
    {
        $groupedAes = ArrayUtils::groupBy(
            $aes,
            function (AuthorisationForAuthorisedExaminerEntity $ae) {
                return $ae->getId();
            }
        );

        return ArrayUtils::map(
            $groupedAes,
            function (array $aes) {
                return $aes[0];
            }
        );
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesById
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesForDesignatedManager
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesForDelegate
     * @param string                                     $aedmRoleName
     * @param string                                     $aedRoleName
     *
     * @return string[]
     */
    private function getAesPositionNames(
        $aesById,
        $aesForDesignatedManager,
        $aesForDelegate,
        $aedmRoleName,
        $aedRoleName
    ) {
        $aesForDesignatedManagerIds = $this->getAesIdsForAes($aesForDesignatedManager);
        $aesForDelegateIds = $this->getAesIdsForAes($aesForDelegate);

        return ArrayUtils::map(
            $aesById,
            function (AuthorisationForAuthorisedExaminerEntity $ae) use (
                $aesForDesignatedManagerIds,
                $aesForDelegateIds,
                $aedmRoleName,
                $aedRoleName
            ) {
                $orgId = $ae->getId();
                if (in_array($orgId, $aesForDesignatedManagerIds)) {

                    return $aedmRoleName;
                } elseif (in_array($orgId, $aesForDelegateIds)) {

                    return $aedRoleName;
                } else {

                    return '';
                }
            }
        );
    }

    /**
     * @param AuthorisationForAuthorisedExaminerEntity[] $aesById
     * @param int                                        $personId
     * @param Site[][]                                   $sitesByAe
     * @param SiteBusinessRoleMap[][]                    $positionsBySite
     * @param string[]                                   $aesPositionNames
     *
     * @return AuthorisationForAuthorisedExaminer[]
     */
    public function getAesWithSitesAndPositions($aesById, $personId, $sitesByAe, $positionsBySite, $aesPositionNames)
    {
        return ArrayUtils::map(
            $aesById,
            function (AuthorisationForAuthorisedExaminerEntity $authorisedExaminer) use (
                $personId,
                $sitesByAe,
                $positionsBySite,
                $aesPositionNames
            ) {
                $designatedManager = $authorisedExaminer->getDesignatedManager();
                $sitesWithPositions = ArrayUtils::map(
                    $sitesByAe[$authorisedExaminer->getId()],
                    function (Site $site) use ($positionsBySite) {

                        return new \UserApi\Dashboard\Dto\Site(
                            $site,
                            ArrayUtils::tryGet($positionsBySite, $site->getId(), [])
                        );
                    }
                );

                return new AuthorisationForAuthorisedExaminer(
                    $authorisedExaminer,
                    ($designatedManager ? $designatedManager->getId() : null),
                    $sitesWithPositions,
                    $aesPositionNames[$authorisedExaminer->getId()],
                    $personId
                );
            }
        );
    }
}
