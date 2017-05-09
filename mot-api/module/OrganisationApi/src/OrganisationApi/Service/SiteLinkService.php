<?php

namespace OrganisationApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaEntities\Entity\EventOrganisationMap;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationSiteMap;
use DvsaEntities\Entity\OrganisationSiteStatus;
use DvsaEntities\Entity\Site;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\OrganisationSiteMapRepository;
use DvsaEntities\Repository\OrganisationSiteStatusRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use OrganisationApi\Service\Mapper\OrganisationSiteLinkMapper;
use OrganisationApi\Service\Validator\SiteLinkValidator;
use SiteApi\Service\MotTestInProgressService;

class SiteLinkService extends AbstractService
{
    const ERR_UNLINK_SITE_TEST_IN_PROGRESS = <<<'ERR_MSG'
An MOT Test is in progress at the selected Site and the Site cannot be removed from the AE until the test is complete
ERR_MSG;

    /**
     * @var AuthorisationServiceInterface
     */
    protected $authService;
    /**
     * @var MotIdentityInterface
     */
    private $identity;
    /**
     * @var EventService
     */
    private $eventService;
    /**
     * @var NotificationService
     */
    private $notificationService;
    /**
     * @var MotTestInProgressService
     */
    private $motTestInProgressService;
    /**
     * @var OrganisationRepository
     */
    private $orgRepo;
    /**
     * @var SiteRepository
     */
    private $siteRepo;
    /**
     * @var OrganisationSiteMapRepository
     */
    private $orgSiteMapRepo;
    /**
     * @var OrganisationSiteStatusRepository
     */
    private $orgSiteStatusRepo;
    /**
     * @var OrganisationSiteLinkMapper
     */
    private $orgSiteLinkMapper;
    /**
     * @var SiteLinkValidator
     */
    private $validator;
    /**
     * @var DateTimeHolder
     */
    private $dateTimeHolder;

    public function __construct(
        EntityManager $entityManager,
        AuthorisationServiceInterface $authSrv,
        MotIdentityInterface $motIdentity,
        EventService $eventService,
        NotificationService $notificationService,
        MotTestInProgressService $motTestInProgressService,
        OrganisationRepository $organisationRepository,
        SiteRepository $siteRepository,
        OrganisationSiteMapRepository $orgSiteMapRepository,
        OrganisationSiteStatusRepository $orgSiteStatusRepository,
        OrganisationSiteLinkMapper $orgSiteLinkMapper,
        SiteLinkValidator $validator,
        DateTimeHolder $dateTimeHolder
    ) {
        parent::__construct($entityManager);

        $this->authService = $authSrv;
        $this->identity = $motIdentity;

        $this->eventService = $eventService;
        $this->notificationService = $notificationService;
        $this->motTestInProgressService = $motTestInProgressService;

        $this->orgRepo = $organisationRepository;
        $this->siteRepo = $siteRepository;
        $this->orgSiteMapRepo = $orgSiteMapRepository;
        $this->orgSiteStatusRepo = $orgSiteStatusRepository;

        $this->orgSiteLinkMapper = $orgSiteLinkMapper;
        $this->validator = $validator;

        $this->dateTimeHolder = $dateTimeHolder;
    }

    /**
     * Search All approved Site Not Linked to an ae.
     *
     * @return array
     */
    public function getApprovedUnlinkedSite()
    {
        $this->authService->assertGranted(PermissionInSystem::VEHICLE_TESTING_STATION_SEARCH);

        return $this->siteRepo->getApprovedUnlinkedSite();
    }

    public function get($id, $status = null)
    {
        $mapEntity = $this->orgSiteMapRepo->get($id, $status);

        $this->authService->assertGrantedAtOrganisation(
            PermissionAtOrganisation::AUTHORISED_EXAMINER_READ, $mapEntity->getOrganisation()->getId()
        );

        $this->authService->assertGrantedAtSite(
            PermissionAtSite::VEHICLE_TESTING_STATION_READ, $mapEntity->getSite()->getId()
        );

        return $this->orgSiteLinkMapper->toDto($mapEntity);
    }

    /**
     * Establishes a logical link between the Organisation(AE) ($orgId) and the Site ($siteNumber)
     * if the site is not currently linked.
     *
     * @param int    $orgId
     * @param string $siteNumber
     *
     * @return bool
     *
     * @throws BadRequestException
     */
    public function siteLink($orgId, $siteNumber)
    {
        $this->authService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_SITE_LINK, $orgId);

        /** @var Organisation $organisation */
        $organisation = $this->orgRepo->get($orgId);

        /** @var Site $site */
        $site = $this->siteRepo->getBySiteNumber($siteNumber);

        //  logical block :: validation
        $this->validator->validateLink($organisation, $site, $orgId, $siteNumber);

        /** @var OrganisationSiteStatus $status */
        $status = $this->orgSiteStatusRepo->getByCode(OrganisationSiteStatusCode::ACTIVE);

        //  logical block :: create link (association) between AE and Site
        $emConn = $this->getEntityManager()->getConnection();
        $emConn->beginTransaction();

        try {
            $lastAssessment = $site->getLastSiteAssessment();
            if (!is_null($lastAssessment) && $lastAssessment->getAeOrganisationId() !== $organisation->getId()) {
                $site->setLastSiteAssessment(null);
            }

            $site->setOrganisation($organisation);

            $map = new OrganisationSiteMap();
            $map
                ->setOrganisation($organisation)
                ->setSite($site)
                ->setTradingName($organisation->getTradingAs())
                ->setStartDate($this->dateTimeHolder->getCurrent())
                ->setStatus($status)
                ->setStatusChangedOn($this->dateTimeHolder->getCurrent());

            //  create event
            $this->createLinkEvent(
                $organisation,
                $site,
                EventTypeCode::DVSA_ADMINISTRATOR_LINK_A_SITE_TO_AN_AE,
                sprintf(
                    EventDescription::DVSA_ADMINISTRATOR_LINK_A_SITE_TO_AN_AE,
                    $siteNumber,
                    $site->getName(),
                    $organisation->getAuthorisedExaminer()->getNumber(),
                    $organisation->getName(),
                    $this->getUserName()
                )
            );

            // persist site changes
            $this->entityManager->persist($site);

            // persist map object
            $this->entityManager->persist($map);
            $this->entityManager->flush();

            //  create notification and !!!    FLUSH   !!!
            $this->createLinkNotification($organisation, $site, Notification::TEMPLATE_DVSA_USER_LINK_SITE_TO_AE);

            $emConn->commit();
        } catch (\Exception $e) {
            $emConn->rollback();
            throw $e;
        }

        return ['id' => $map->getId()];
    }

    /**
     * Change status of link (association) between Organisation (AE) and Site.
     * If status is Withdraw or Surrendered then it is mean association was removed.
     *
     * @param int    $linkId
     * @param string $statusCode see OrganisationSiteStatusCode enum
     *
     * @return bool
     *
     * @throws NotFoundException
     * @throws ServiceException
     */
    public function siteChangeStatus($linkId, $statusCode)
    {
        //  logical block :: get data
        /** @var \DvsaEntities\Entity\OrganisationSiteMap $mapEntity */
        $mapEntity = $this->orgSiteMapRepo->get($linkId, OrganisationSiteStatusCode::ACTIVE);

        $orgEntity = $mapEntity->getOrganisation();
        $siteEntity = $mapEntity->getSite();

        //  check permissions
        $this->authService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_SITE_UNLINK, $orgEntity->getId());

        //  logical block :: validation
        $this->validator->validateUnlink($statusCode);

        //  logical block :: check has active mot test
        $countMotTestInProgress = $this->motTestInProgressService->getCountForSite($siteEntity->getId());
        if ($countMotTestInProgress !== 0) {
            throw (new ServiceException(self::ERR_UNLINK_SITE_TEST_IN_PROGRESS))
                ->addError(self::ERR_UNLINK_SITE_TEST_IN_PROGRESS, ServiceException::BAD_REQUEST_STATUS_CODE);
        }

        //  logical block :: change status of link (association) and do related stuff
        $emConn = $this->getEntityManager()->getConnection();
        $emConn->beginTransaction();

        try {
            $mapEntity->setStatus($this->orgSiteStatusRepo->getByCode($statusCode))
                ->setStatusChangedOn($this->dateTimeHolder->getCurrent())
                ->setEndDate($this->dateTimeHolder->getCurrent());

            //  remove organisation from site
            $siteEntity->setOrganisation(null);

            //  create event
            $eventDesc = sprintf(
                EventDescription::AE_UNLINK_SITE,
                $siteEntity->getSiteNumber(),
                $siteEntity->getName(),
                $orgEntity->getAuthorisedExaminer()->getNumber(),
                $orgEntity->getName(),
                $this->getUserName()
            );
            $this->createLinkEvent($orgEntity, $siteEntity, EventTypeCode::UNLINK_AE_SITE, $eventDesc);

            //  store in db
            $this->entityManager->persist($siteEntity);
            $this->entityManager->persist($mapEntity);

            //  create notification and !!!    FLUSH   !!!
            $this->createLinkNotification($orgEntity, $siteEntity, Notification::TEMPLATE_AE_UNLINK_SITE);

            $emConn->commit();
        } catch (\Exception $e) {
            $emConn->rollback();
            throw $e;
        }

        return true;
    }

    /**
     * Create site event.
     *
     * @param Organisation $org
     * @param Site         $site
     * @param string       $eventType
     * @param string       $eventDesc
     *
     * @throws \Exception
     */
    private function createLinkEvent(Organisation $org, Site $site, $eventType, $eventDesc)
    {
        $event = $this->eventService->addEvent(
            $eventType,
            $eventDesc,
            $this->dateTimeHolder->getCurrent(true)
        );

        $eventSiteMap = (new EventSiteMap())
            ->setEvent($event)
            ->setSite($site);

        $eventOrgMap = (new EventOrganisationMap())
            ->setEvent($event)
            ->setOrganisation($org);

        $this->entityManager->persist($eventSiteMap);
        $this->entityManager->persist($eventOrgMap);
    }

    /**
     * Create a notification for the manager of the Ae.
     *
     * @param Organisation $org
     * @param Site         $site
     * @param int          $template
     */
    private function createLinkNotification(Organisation $org, Site $site, $template)
    {
        if (is_null($org->getAuthorisedExaminer())
            || is_null($org->getAuthorisedExaminer()->getDesignatedManager())) {
            return;
        }

        $data = (new Notification())
            ->setRecipient($org->getAuthorisedExaminer()->getDesignatedManager()->getId())
            ->setTemplate($template)
            ->addField('siteNr', $site->getSiteNumber())
            ->addField('siteName', $site->getName())
            ->addField('aeNr', $org->getAuthorisedExaminer()->getNumber())
            ->addField('aeName', $org->getName());

        $this->notificationService->add($data);
    }

    private function getUserName()
    {
        return $this->identity->getUsername();
    }
}
