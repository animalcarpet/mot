<?php

namespace SiteApi\Service;

use DvsaCommon\Enum\EventCategoryCode;
use DvsaEntities\Entity\EventSiteMap;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\Event;
use DvsaEventApi\Service\EventService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Repository\SiteRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaEventApi\Service\RecordEventResult;

class SiteEventService
{
    /**
     * @var EventService
     */
    private $eventService;

    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var SiteRepository
     */
    private $siteRepository;

    public function __construct(
        EventService $eventService,
        AuthorisationServiceInterface $authService,
        EntityManager $entityManager,
        SiteRepository $siteRepository
    ) {
        $this->eventService = $eventService;
        $this->authService = $authService;
        $this->entityManager = $entityManager;
        $this->siteRepository = $siteRepository;
    }

    /**
     * @param int   $siteId
     * @param array $data
     *
     * @return RecordEventResult
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    public function create($siteId, array $data)
    {
        $this->authService->assertGranted(PermissionInSystem::EVENT_CREATE);
        $this->assertEventCategory($data);

        $site = $this->getSiteEntity($siteId);

        $this->entityManager->beginTransaction();

        try {
            /** @var Event $event */
            $event = $this->eventService->recordManualEvent($data);
            $this->createEventSiteMap($site, $event);
            $this->entityManager->commit();

            return new RecordEventResult($event);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @param Site  $site
     * @param Event $event
     *
     * @return bool
     */
    private function createEventSiteMap(Site $site, Event $event)
    {
        $eventPersonMap = (new EventSiteMap())
            ->setSite($site)
            ->setEvent($event);

        $this->entityManager->persist($eventPersonMap);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Ensures that the category being passed in data is the right one for the entity.
     *
     * @param array $data
     *
     * @return bool
     *
     * @throws \InvalidArgumentException if wrong category supplied
     */
    private function assertEventCategory(array $data)
    {
        if (isset($data['eventCategoryCode']) && $data['eventCategoryCode'] == EventCategoryCode::VTS_EVENT) {
            return true;
        }
        throw new \InvalidArgumentException('Category not recognised');
    }

    /**
     * @param $siteId
     *
     * @return null|Site
     *
     * @throws NotFoundException
     */
    private function getSiteEntity($siteId)
    {
        // SiteRepository
        $person = $this->siteRepository->find($siteId);
        if (!$person instanceof Site) {
            throw new NotFoundException('Unable to find site with id '.$siteId);
        }

        return $person;
    }
}
