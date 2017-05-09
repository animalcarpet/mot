<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\IdentityProvider;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\MockRepositoryHelper;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\OrganisationPositionHistoryRepository;
use DvsaEntities\Repository\OrganisationRepository;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use OrganisationApi\Service\Mapper\OrganisationPositionMapper;
use OrganisationApi\Service\OrganisationPositionService;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEventApi\Service\EventService;

class OrganisationPositionServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $userOrganisationNotificationService;
    private $organisationPositionRepository;
    private $positionHistoryRepository;
    private $organisationRepository;
    private $organisationPositionMapper;
    private $notificationService;
    private $identityProvider;
    private $authorisationService;
    private $eventService;
    private $positionRemovalNotificationService;
    private $entityManager;

    /** @var OrganisationPositionService */
    private $organisationPositionService;

    public function setUp()
    {
        $this->positionHistoryRepository = XMock::of(OrganisationPositionHistoryRepository::class);
        $this->organisationPositionRepository = XMock::of(OrganisationBusinessRoleMapRepository::class);
        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this->organisationPositionMapper = XMock::of(OrganisationPositionMapper::class);
        $this->notificationService = XMock::of(NotificationService::class);
        $this->entityManager = XMock::of(EntityManager::class);

        $this->identityProvider = XMock::of(IdentityProvider::class);
        $identityMock = XMock::of(MotIdentityInterface::class);
        $identityMock->expects($this->any())->method('getUserId')->willReturn(9898998);
        $this->identityProvider->expects($this->any())->method('getIdentity')->willReturn($identityMock);

        $this->authorisationService = XMock::of(AuthorisationService::class);
        $this->eventService = XMock::of(EventService::class);
        $this->positionRemovalNotificationService = XMock::of(PositionRemovalNotificationService::class);
        $this->userOrganisationNotificationService = new UserOrganisationNotificationService(
            $this->notificationService,
            $this->positionRemovalNotificationService
        );
        $this->organisationPositionService = $this->buildService();
    }

    /**
     * @return OrganisationPositionService
     */
    private function buildService()
    {
        $service = new OrganisationPositionService(
            $this->organisationRepository,
            $this->organisationPositionRepository,
            $this->positionHistoryRepository,
            $this->organisationPositionMapper,
            $this->identityProvider,
            $this->authorisationService,
            $this->entityManager,
            $this->eventService,
            $this->positionRemovalNotificationService,
            $this->userOrganisationNotificationService
        );

        return $service;
    }

    public function testRemove_givenValidPosition_properNotificationSent()
    {
        list($positionId, $orgId, $recipientId) = [5, 4, 32];
        $position = $this->validOrganisationPosition($orgId);
        $position->getPerson()->setId($recipientId);

        $this->returnsPosition($positionId, $position);
        $notificationPromise = $this->notificationSent();

        $this->organisationPositionRepository->expects($this->any())
                                             ->method('find')
                                             ->with($positionId)
                                             ->willReturn($position);

        $this->organisationPositionService->remove($orgId, $positionId);

        /** @var array $notification */
        $notification = $notificationPromise->get();
        $this->assertEquals($recipientId, $notification['recipient']);
        $this->assertEquals(Notification::TEMPLATE_ORGANISATION_POSITION_REMOVED, $notification['template']);
    }

    public function testRemove_givenInvalidPosition_doNotPersistPosition()
    {
        list($positionId, $orgId) = [5, 4];
        $organisationPosition = $this->validOrganisationPosition($orgId);
        $this->returnsPosition($positionId, $organisationPosition);
        MockRepositoryHelper::assertNoPersist($this->positionHistoryRepository, $this->organisationPositionRepository);

        $this->setExpectedException(NotFoundException::class);

        $this->organisationPositionRepository->expects($this->any())
                                             ->method('find')
                                             ->with($positionId)
                                             ->willReturn($organisationPosition);

        $service = $this->buildService();
        $this->organisationPositionService->remove($orgId + 1, $positionId);

        $this->assertFalse(TestTransactionExecutor::isFlushed($service));
    }

    private function returnsPosition($positionId, $position)
    {
        $this->organisationPositionRepository->expects($this->atLeastOnce())
            ->method('find')->with($positionId)->will($this->returnValue($position));
    }

    private function notificationSent()
    {
        $capNotification = ArgCapture::create();
        $this->notificationService->expects($this->atLeastOnce())->method('add')
            ->with($capNotification());

        return $capNotification;
    }

    private function validOrganisationPosition($organisationId)
    {
        $person = new Person();

        $authExaminer = (new AuthorisationForAuthorisedExaminer())->setNumber('AE123');

        $org = (new Organisation())->setId($organisationId);
        $org->setAuthorisedExaminer($authExaminer);

        $role = new OrganisationBusinessRole();
        $role->setName(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);

        $status = new BusinessRoleStatus();
        $status->setCode('AC');

        $map = new OrganisationBusinessRoleMap();
        $map->setPerson($person);
        $map->setOrganisation($org);
        $map->setOrganisationBusinessRole($role);
        $map->setBusinessRoleStatus($status);

        return $map;
    }
}
