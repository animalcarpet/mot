<?php

namespace SiteApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Constants\EventDescription;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\RoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Stub\ApiIdentityProviderStub;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\Auth\AuthorisationServiceMock;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use DvsaEventApi\Service\EventService;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use SiteApi\Service\SitePositionService;

class RemoveOwnSitePositionTest extends \PHPUnit_Framework_TestCase
{
    protected $userOrganisationNotificationService;
    /**
     * @var AuthorisationServiceMock
     */
    private $authorisationService;
    private $notificationService;
    /** @var \PHPUnit_Framework_MockObject_MockObject | SiteBusinessRoleMapRepository */
    private $siteBusinessRoleMapRepository;
    private $eventService;
    private $entityManager;
    private $positionRemovalNotificationService;

    /** @var ApiIdentityProviderStub */
    private $identityProvider;

    private $myId = 25;
    private $myPositionId = 324;
    /** @var Person */
    private $me;
    /** @var Site */
    private $vtsA;
    /** @var Site */
    private $vtsB;

    /** @var \PHPUnit_Framework_MockObject_MockObject | MotTestRepository */
    private $motTestRepository;

    /** @var MethodSpy */
    private $submitEventSpy;

    public function setUp()
    {
        $this->me = new Person();
        $this->me->setId($this->myId);
        $this->vtsA = new Site();
        $this->vtsA->setId(2);

        $this->vtsB = new Site();
        $this->vtsB->setId(4);

        $this->authorisationService = new AuthorisationServiceMock();
        $this->notificationService = XMock::of(NotificationService::class);
        $this->siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $this->entityManager = XMock::of(EntityManager::class);
        $this->eventService = XMock::of(EventService::class);
        $this->positionRemovalNotificationService = XMock::of(PositionRemovalNotificationService::class);
        $this->identityProvider = new ApiIdentityProviderStub();
        $this->motTestRepository = XMock::of(MotTestRepository::class);

        $identityMock = XMock::of(MotIdentityInterface::class);
        $identityMock->expects($this->any())->method('getUserId')->willReturn(101);

        $this->userOrganisationNotificationService = new UserOrganisationNotificationService(
            $this->notificationService,
            $this->positionRemovalNotificationService
        );

        $this->submitEventSpy = new MethodSpy($this->eventService, 'addEvent');
    }

    private function buildService()
    {
        return new SitePositionService(
            $this->eventService,
            $this->siteBusinessRoleMapRepository,
            $this->authorisationService,
            $this->entityManager,
            $this->notificationService,
            $this->identityProvider,
            $this->motTestRepository,
            $this->userOrganisationNotificationService
        );
    }

    public function testRemoveOwnPositionSuccessfully()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I have a position in a VTS
        $notificationRecipient = rand(1, 10000);
        $position = $this->createMySitePosition($this->vtsA, SiteBusinessRoleCode::TESTER, $notificationRecipient, RoleCode::SITE_MANAGER);
        $this->siteBusinessRoleMapRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => $this->myPositionId])
            ->willReturn($position);

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));
        $notificationPromise = $this->notificationSent();

        // WHEN I remove my position
        $this->buildService()->remove($this->vtsA->getId(), $this->myPositionId);

        // Then the position is removed
        $this->assertGreaterThan(0, $removeEntitySpy->invocationCount());
        $this->assertEquals($position, $removeEntitySpy->paramsForLastInvocation()[0]);

        // AND notification is sent
        $notification = $notificationPromise->get();
        $this->assertEquals(Notification::TEMPLATE_USER_REMOVED_OWN_ROLE, $notification['template']);
        $this->assertEquals($notificationRecipient, $notification['recipient']);

        // AND event is sent
        $this->assertEventsWereSent();
    }

    public function testRemoveOwnTesterPositionSuccessfullyWhenUserITestInProgressInOtherSite()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I have a position in a VTS
        $notificationRecipient = rand(1, 10000);
        $position = $this->createMySitePosition($this->vtsA, SiteBusinessRoleCode::TESTER, $notificationRecipient, RoleCode::SITE_MANAGER);
        $this->siteBusinessRoleMapRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => $this->myPositionId])
            ->willReturn($position);

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));
        $notificationPromise = $this->notificationSent();

        // AND I have test in progress but in a different site
        $motTest = new MotTest();
        $motTest->setVehicleTestingStation($this->vtsB);
        $this->motTestRepository->expects($this->any())->method('findInProgressTestForPerson')
            ->with($this->myId)->willReturn($motTest);

        // WHEN I remove my position
        $this->buildService()->remove($this->vtsA->getId(), $this->myPositionId);

        // Then the position is removed
        $this->assertGreaterThan(0, $removeEntitySpy->invocationCount());
        $this->assertEquals($position, $removeEntitySpy->paramsForLastInvocation()[0]);

        // AND notification is sent
        $notification = $notificationPromise->get();
        $this->assertEquals(Notification::TEMPLATE_USER_REMOVED_OWN_ROLE, $notification['template']);
        $this->assertEquals($notificationRecipient, $notification['recipient']);

        // AND event is sent
        $this->assertEventsWereSent();
    }

    public function testRemoveOwnNonTesterPositionSuccessfullyWhenIHaveTestInProgressInTheGivenSite()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I have a position in a VTS
        $notificationRecipient = rand(1, 10000);
        $position = $this->createMySitePosition($this->vtsA, SiteBusinessRoleCode::SITE_MANAGER, $notificationRecipient, RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER);
        $this->siteBusinessRoleMapRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => $this->myPositionId])
            ->willReturn($position);

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));
        $notificationPromise = $this->notificationSent();

        // AND I have test in progress in the same site
        $motTest = new MotTest();
        $motTest->setVehicleTestingStation($this->vtsA);
        $this->motTestRepository->expects($this->any())->method('findInProgressTestForPerson')
            ->with($this->myId)->willReturn($motTest);

        // WHEN I remove my position
        $this->buildService()->remove($this->vtsA->getId(), $this->myPositionId);

        // Then the position is removed
        $this->assertGreaterThan(0, $removeEntitySpy->invocationCount());
        $this->assertEquals($position, $removeEntitySpy->paramsForLastInvocation()[0]);

        // AND notification is sent
        $notification = $notificationPromise->get();
        $this->assertEquals(Notification::TEMPLATE_USER_REMOVED_OWN_ROLE, $notification['template']);
        $this->assertEquals($notificationRecipient, $notification['recipient']);

        // AND event is sent
        $this->assertEventsWereSent();
    }

    public function testRemoveOwnPositionFailsWhenPositionDoesNotExist()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I do not have a position in a VTS

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));

        // WHEN I remove my position
        try {
            $this->buildService()->remove($this->vtsA->getId(), $this->myPositionId);
            $this->fail('Exception was expected');
        } catch (BadRequestException $e) {
            // THEN validation exception is thrown with message stating that
            // role couldn't be removed because it doesn't exist anymore

            $this->assertEquals('This role has already been removed', $e->getErrors()[0]['message']);
        }

        // THEN no positions are removed
        $this->assertEquals(0, $removeEntitySpy->invocationCount());

        // AND no notifications are sent
        $this->notificationService->expects($this->never())->method('add');

        // AND no events are sent
        $this->assertEventsWereNotSent();
    }

    public function testRemoveOwnTesterPositionFailsWhenThereIsATestInProgress()
    {
        $removeEntitySpy = new MethodSpy($this->entityManager, 'remove');

        // GIVEN I have a tester position in a VTS
        $position = $this->createMySitePosition($this->vtsA, SiteBusinessRoleCode::TESTER, 1, RoleCode::SITE_MANAGER);
        $this->siteBusinessRoleMapRepository->expects($this->any())
            ->method('findOneBy')
            ->with(['id' => $this->myPositionId])
            ->willReturn($position);

        // AND I am the one who removes the role
        $this->identityProvider->setIdentity(new Identity($this->me));

        // AND I have test in progress in that site
        $motTest = new MotTest();
        $motTest->setVehicleTestingStation($this->vtsA);
        $this->motTestRepository
            ->expects($this->any())
            ->method('findInProgressTestForPerson')
            ->with($this->myId)
            ->willReturn($motTest);

        // WHEN I remove my position
        try {
            $this->buildService()->remove($this->vtsA->getId(), $this->myPositionId);
            $this->fail('Exception was expected');
        } catch (BadRequestException $e) {
            // THEN validation exception is thrown with message stating that
            // role couldn't be removed because I have a test in progress

            $this->assertEquals('You currently have a vehicle registered for test or retest. This must be completed or aborted before you can remove this role.', $e->getErrors()[0]['message']);
        }

        // AND no positions are removed
        $this->assertEquals(0, $removeEntitySpy->invocationCount());

        // AND no notifications are sent
        $this->notificationService->expects($this->never())->method('add');

        // AND no events are sent
        $this->assertEventsWereNotSent();
    }

    private function createMySitePosition(Site $vts, $roleCode, $notificationRecipientId, $sitePositionCode)
    {
        $person = new Person();
        $person->setId($this->myId);
        $positions = new ArrayCollection();
        $role = new SiteBusinessRole();
        $role->setCode($sitePositionCode);
        $siteBusinessRoleMap = (new SiteBusinessRoleMap());
        $siteBusinessRoleMap->setPerson((new Person())->setId($notificationRecipientId));
        $positions->add($siteBusinessRoleMap->setSiteBusinessRole($role));
        $vts->setPositions($positions);
        $role = new SiteBusinessRole();
        $role->setCode($roleCode);
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $map = new SiteBusinessRoleMap();
        $map->setId($this->myId);
        $map->setPerson($person);
        $map->setSite($vts);
        $map->setSiteBusinessRole($role);
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
        $expectedContent = sprintf(EventDescription::ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG, null, null, null, null, null);
        $this->assertEquals($params[1], $expectedContent);

        $paramsSite = $this->submitEventSpy->paramsForInvocation(1);
        $expectedSiteContent = sprintf(EventDescription::ROLE_SELF_ASSOCIATION_REMOVE_SITE_ORG, null, null, null, null, null);
        $this->assertEquals($paramsSite[1], $expectedSiteContent);
    }

    private function assertEventsWereNotSent()
    {
        $this->assertEquals(0, $this->submitEventSpy->invocationCount());
    }
}
