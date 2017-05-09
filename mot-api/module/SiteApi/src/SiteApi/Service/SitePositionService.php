<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Auth\AbstractMotAuthorisationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\EventTypeCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Validator\ErrorSchema;
use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionAwareTrait;
use DvsaEntities\Entity\EventPersonMap;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;

/**
 * Takes care of operations associated with site position.
 */
class SitePositionService implements TransactionAwareInterface
{
    use TransactionAwareTrait;

    /** @var EntityManager */
    private $entityManager;
    /** @var NotificationService */
    private $notificationService;
    /** @var EventService $eventService */
    private $eventService;

    /**
     * @var AbstractMotAuthorisationService
     */
    private $authorisationService;

    private $identityProvider;

    private $motTestRepository;

    /**
     * @var UserOrganisationNotificationService
     */
    protected $userOrganisationNotificationService;

    public function __construct(
        EventService $eventService,
        SiteBusinessRoleMapRepository $siteBusinessRoleMapRepository,
        MotAuthorisationServiceInterface $authorisationService,
        EntityManager $entityManager,
        NotificationService $notificationService,
        MotIdentityProviderInterface $identityProvider,
        MotTestRepository $motTestRepository,
        UserOrganisationNotificationService $userOrganisationNotificationService
    ) {
        $this->eventService = $eventService;
        $this->siteBusinessRoleMapRepository = $siteBusinessRoleMapRepository;
        $this->authorisationService = $authorisationService;
        $this->entityManager = $entityManager;
        $this->notificationService = $notificationService;
        $this->identityProvider = $identityProvider;
        $this->motTestRepository = $motTestRepository;
        $this->userOrganisationNotificationService = $userOrganisationNotificationService;
    }

    /**
     * Removes role in a site
     * + TODO: asserts operation possible for the current user
     * + asserts valid SiteBusinessRoleMap
     * + removes physical SiteBusinessRoleMap entity
     * + adds SitePositionHistory entity in REMOVED status
     * + sends removal notification.
     *
     * @param int $siteId
     * @param int $mapId
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function remove($siteId, $mapId)
    {
        /** @var SiteBusinessRoleMap $position */
        $position = $this->siteBusinessRoleMapRepository->findOneBy(['id' => $mapId]);

        if ($position) {
            $this->assertValidPositionInSite($position, $siteId);
            $this->assertCanRemovePosition($position);
            $this->assertTestInProgress($position);

            if ($this->identityProvider->getIdentity()->getUserId() == $position->getPerson()->getId()) {
                $this->userOrganisationNotificationService->notifySiteAboutRoleRemoval($position);
            } else {
                $this->userOrganisationNotificationService->sendNotificationToUserAboutSiteRoleRemoval($position);
            }

            $this->submitEvent($position);

            $this->entityManager->remove($position);
            $this->entityManager->flush();
        } else {
            ErrorSchema::throwError('This role has already been removed');
        }
    }

    private function assertTestInProgress(SiteBusinessRoleMap $position)
    {
        $personId = $position->getPerson()->getId();
        $siteId = $position->getSite()->getId();

        if ($position->getSiteBusinessRole()->getCode() == SiteBusinessRoleCode::TESTER) {
            $testInProgress = $this->motTestRepository->findInProgressTestForPerson($personId);
            if ($testInProgress) {
                if ($testInProgress->getVehicleTestingStation()->getId() == $siteId) {
                    $person = $position->getPerson();
                    $errorMessage = $person->getId() == $this->identityProvider->getIdentity()->getUserId()
                        ? 'You currently have a vehicle registered for test or retest. This must be completed or aborted before you can remove this role.'
                        : $person->getDisplayName()
                        .' currently has a vehicle registered for test or retest. This must be completed or aborted before their role can be removed.';

                    ErrorSchema::throwError($errorMessage);
                }
            }
        }
    }

    private function assertCanRemovePosition(SiteBusinessRoleMap $position)
    {
        if ($this->authorisationService->isGrantedAtSite(
                PermissionAtSite::REMOVE_ROLE_AT_SITE,
                $position->getSite()->getId()
            )
            || $position->getPerson()->getId() == $this->identityProvider->getIdentity()->getUserId()
        ) {
            return;
        }

        throw new UnauthorisedException('No permissions to remove role');
    }

    private function assertValidPositionInSite(SiteBusinessRoleMap $map, $siteId)
    {
        if ($map->getSite()->getId() !== $siteId) {
            throw new BadRequestException(
                'Invalid relation between site and role map',
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }
    }

    private function submitEvent(SiteBusinessRoleMap $siteRoleMap)
    {
        // Person Level
        $event = $this->eventService->addEvent(
            EventTypeCode::ROLE_ASSOCIATION_CHANGE,
            sprintf(
                EventDescription::ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG,
                $siteRoleMap->getSiteBusinessRole()->getName(),
                $siteRoleMap->getPerson()->getDisplayName(),
                $siteRoleMap->getPerson()->getUsername(),
                $siteRoleMap->getSite()->getName(),
                $siteRoleMap->getSite()->getSiteNumber()
            ),
            new \DateTime()
        );

        $eventPersonMap = new EventPersonMap();
        $eventPersonMap->setEvent($event)
            ->setPerson($siteRoleMap->getPerson());

        $this->entityManager->persist($eventPersonMap);

        // Site Level
        $event = $this->eventService->addEvent(
            EventTypeCode::ROLE_ASSOCIATION_CHANGE,
            sprintf(
                EventDescription::ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG,
                $siteRoleMap->getSiteBusinessRole()->getName(),
                $siteRoleMap->getPerson()->getDisplayName(),
                $siteRoleMap->getPerson()->getUsername(),
                $siteRoleMap->getSite()->getName(),
                $siteRoleMap->getSite()->getSiteNumber()
            ),
            new \DateTime()
        );

        $eventSiteMap = new EventSiteMap();
        $eventSiteMap->setEvent($event)
            ->setSite($siteRoleMap->getSite());

        $this->entityManager->persist($eventSiteMap);
        $this->entityManager->flush();
    }
}
