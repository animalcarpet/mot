<?php

namespace OrganisationApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Identity;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Stub\ApiIdentityProviderStub;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Role;
use DvsaEntities\Repository\OrganisationBusinessRoleMapRepository;
use DvsaEntities\Repository\OrganisationPositionHistoryRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use OrganisationApi\Service\Mapper\OrganisationPositionMapper;
use OrganisationApi\Service\OrganisationPositionService;

class RemoveOwnOrganisationPositionServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $userOrganisationNotificationService;
    protected $submitEventSpy;
    const AE_NUMBER = 'AE123';
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $organisationPositionRepository;
    private $positionHistoryRepository;
    private $organisationRepository;
    private $organisationPositionMapper;
    private $notificationService;
    /** @var ApiIdentityProviderStub */
    private $identityProvider;
    private $authorisationService;
    private $eventService;
    private $positionRemovalNotificationService;
    private $entityManager;

    private $myId = 25;
    private $myPositionId = 324;
    private $myAeId = 11;

    /** @var Person */
    private $me;

    /** @var OrganisationPositionService */
    private $organisationPositionService;

    public function setUp()
    {
        $this->me = new Person();
        $this->me->setId($this->myId);

        $this->positionHistoryRepository = XMock::of(OrganisationPositionHistoryRepository::class);
        $this->organisationPositionRepository = XMock::of(OrganisationBusinessRoleMapRepository::class);
        $this->organisationRepository = XMock::of(OrganisationRepository::class);
        $this->organisationPositionMapper = XMock::of(OrganisationPositionMapper::class);
        $this->notificationService = XMock::of(NotificationService::class);
        $this->entityManager = XMock::of(EntityManager::class);
        $this->identityProvider = new ApiIdentityProviderStub();
        $this->authorisationService = new AuthorisationServiceMock();
        $this->eventService = XMock::of(EventService::class);
        $this->positionRemovalNotificationService = XMock::of(PositionRemovalNotificationService::class);
        $this->userOrganisationNotificationService = new UserOrganisationNotificationService(
            $this->notificationService,
            $this->positionRemovalNotificationService
        );

        $this->submitEventSpy = new MethodSpy($this->eventService, 'addEvent');
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

    public function testRemoveOwnPositionSuccessfully()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I have a position in an AE
        $notificationRecipient = rand(1, 10000);
        $position = $this->createMyOrganisationPosition(
            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE,
            $notificationRecipient,
            RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER
        );
        $this->organisationPositionRepository->expects($this->any())
            ->method('find')
            ->with($this->myPositionId)
            ->willReturn($position);

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));
        $notificationPromise = $this->notificationSent();

        // WHEN I remove my position
        $this->buildService()->remove($this->myAeId, $this->myPositionId);

        // Then the position is removed
        $this->assertGreaterThan(0, $removeEntitySpy->invocationCount());
        $this->assertEquals($position, $removeEntitySpy->paramsForLastInvocation()[0]);
        // AND notification is sent
        $notification = $notificationPromise->get();
        $this->assertEquals(Notification::TEMPLATE_USER_REMOVED_OWN_ROLE, $notification['template']);
        $this->assertEquals($notificationRecipient, $notification['recipient']);
        //AND event is sent
        $this->assertEventsWereSent();
    }

    public function testRemoveOwnPositionFailsWhenPositionDoesNotExist()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I do not have a position in an AE

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));

        // WHEN I remove my position
        try {
            $this->buildService()->remove($this->myAeId, $this->myPositionId);
            $this->fail('Exception was expected');
        } catch (BadRequestException $e) {
            // THEN validation exception is thrown with message stating that
            // role couldn't be removed because it doesn't exist anymore

            $this->assertEquals('This role has already been removed', $e->getErrors()[0]['message']);
        }

        // AND no positions are removed
        $this->assertEquals(0, $removeEntitySpy->invocationCount());
        // AND no notifications are sent
        $this->notificationService->expects($this->never())->method('add');

        // AND no events are sent
        $this->assertEventsWereNotSent();
    }

    public function testRemoveOwnAedmPositionFails()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I am AEDM in AE
        $position = $this->createMyOrganisationPosition(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);
        $this->organisationPositionRepository->expects($this->any())
            ->method('find')
            ->with($this->myPositionId)
            ->willReturn($position);

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));

        // WHEN I remove my position
        try {
            $this->buildService()->remove($this->myAeId, $this->myPositionId);
            $this->fail('UnauthorisedException was expected');
        } catch (UnauthorisedException $e) {
            // THEN validation exception is thrown with message stating that
            // role couldn't be removed because it doesn't exist anymore
        }

        // AND no positions are removed
        $this->assertEquals(0, $removeEntitySpy->invocationCount());
        // AND no notifications are sent
        $this->notificationService->expects($this->never())->method('add');

        // AND no events are sent
        $this->assertEventsWereNotSent();
    }

    private function createMyOrganisationPosition($roleName, $notificationRecipientId = 0, $sitePositionCode = '')
    {
        $person = new Person();
        $person->setId($this->myId);
        $role = new Role();
        $role->setCode($sitePositionCode);
        $siteBusinessRoleMap = (new OrganisationBusinessRoleMap());
        $siteBusinessRoleMap->setPerson((new Person())->setId($notificationRecipientId));
        $siteBusinessRoleMap->setOrganisationBusinessRole((new OrganisationBusinessRole())->setRole($role));

        $authExaminer = (new AuthorisationForAuthorisedExaminer())->setNumber(self::AE_NUMBER);

        $org = (new Organisation())->setId($this->myAeId);
        $org->setAuthorisedExaminer($authExaminer);
        $org->addPosition($siteBusinessRoleMap);

        $organisationBusinessRole = new OrganisationBusinessRole();
        $organisationBusinessRole->setName($roleName);

        $role = new Role();
        $role->setCode(RoleCode::AUTHORISED_EXAMINER);

        $organisationBusinessRole->setRole($role);

        $status = new BusinessRoleStatus();
        $status->setCode(BusinessRoleStatusCode::ACTIVE);

        $map = new OrganisationBusinessRoleMap();
        $map->setId($this->myPositionId);
        $map->setPerson($this->me);
        $map->setOrganisation($org);
        $map->setOrganisationBusinessRole($organisationBusinessRole);
        $map->setBusinessRoleStatus($status);

        return $map;
    }

    private function notificationSent()
    {
        $capNotification = ArgCapture::create();
        $this->notificationService->expects($this->atLeastOnce())->method('add')
            ->with($capNotification());

        return $capNotification;
    }

    private function assertEventsWereSent()
    {
        $this->assertEquals(2, $this->submitEventSpy->invocationCount());

        $params = $this->submitEventSpy->paramsForInvocation(0);
        $expectedContent = sprintf(EventDescription::ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG, null, null, null, null, self::AE_NUMBER);
        $this->assertEquals($params[1], $expectedContent);

        $paramsSite = $this->submitEventSpy->paramsForInvocation(1);
        $expectedSiteContent = sprintf(EventDescription::ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG, null, null, null, null, self::AE_NUMBER);
        $this->assertEquals($paramsSite[1], $expectedSiteContent);
    }

    private function assertEventsWereNotSent()
    {
        $this->assertEquals(0, $this->submitEventSpy->invocationCount());
    }
}
