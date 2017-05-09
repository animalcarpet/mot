<?php

namespace SiteApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApiTest\Stub\ApiIdentityProviderStub;
use DvsaCommonApiTest\Transaction\TestTransactionExecutor;
use DvsaCommonTest\TestUtils\ArgCapture;
use DvsaCommonTest\TestUtils\Auth\GrantAllAuthorisationServiceStub;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Repository\MotTestRepository;
use DvsaEntities\Repository\SiteBusinessRoleMapRepository;
use NotificationApi\Dto\Notification;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\PositionRemovalNotificationService;
use NotificationApi\Service\UserOrganisationNotificationService;
use SiteApi\Service\SitePositionService;
use DvsaEventApi\Service\EventService;
use DvsaAuthentication\Identity;

/**
 * Class SitePositionServiceTest.
 */
class SitePositionServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $userOrganisationNotificationService;
    /**
     * @var AbstractMotAuthorisationService
     */
    private $authorisationService;
    private $notificationService;
    private $siteBusinessRoleMapRepository;
    private $eventService;
    private $entityManager;
    private $positionRemovalNotificationService;
    /** @var ApiIdentityProviderStub */
    private $identityProvider;

    public function setUp()
    {
        $this->authorisationService = new GrantAllAuthorisationServiceStub();
        $this->notificationService = XMock::of(NotificationService::class);
        $this->siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $this->entityManager = XMock::of(EntityManager::class);
        $this->eventService = XMock::of(EventService::class);
        $this->positionRemovalNotificationService = XMock::of(PositionRemovalNotificationService::class);
        $this->identityProvider = new ApiIdentityProviderStub();

        $this->identityProvider->setIdentity(new Identity(new Person()));

        $this->userOrganisationNotificationService = new UserOrganisationNotificationService(
            $this->notificationService,
            $this->positionRemovalNotificationService
        );
    }

    private function getServiceWithMockServices($siteBusinessRoleMapMock)
    {
        return new SitePositionService(
            $this->eventService,
            $siteBusinessRoleMapMock,
            $this->authorisationService,
            $this->entityManager,
            $this->notificationService,
            $this->identityProvider,
            XMock::of(MotTestRepository::class),
            $this->userOrganisationNotificationService
        );
    }

    public function testRemove_givenValidPosition_properNotificationSent()
    {
        $positionId = 5;
        $orgId = 4;
        $recipientId = 32;

        $position = $this->validSitePosition($orgId);
        $position->getPerson()->setId($recipientId);

        $this->siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $this->siteBusinessRoleMapRepository->expects($this->atLeastOnce())
                                            ->method('findOneBy')
                                            ->with(['id' => $positionId])
                                            ->will($this->returnValue($position));

        $notificationPromise = $this->notificationSent();

        $this->getServiceWithMockServices($this->siteBusinessRoleMapRepository)->remove($orgId, $positionId);

        /** @var array $notification */
        $notification = $notificationPromise->get();
        $this->assertEquals($recipientId, $notification['recipient']);
        $this->assertEquals(Notification::TEMPLATE_SITE_POSITION_REMOVED, $notification['template']);
    }

    public function testRemove_givenInvalidPosition_doNotPersistPosition()
    {
        list($positionId, $orgId) = [5, 4];
        $sitePosition = $this->validSitePosition($orgId);

        $this->siteBusinessRoleMapRepository = XMock::of(SiteBusinessRoleMapRepository::class);
        $this->siteBusinessRoleMapRepository->expects($this->atLeastOnce())
            ->method('findOneBy')
            ->with(['id' => $positionId])
            ->will($this->returnValue($sitePosition));

        $this->setExpectedException(BadRequestException::class);

        $this->getServiceWithMockServices($this->siteBusinessRoleMapRepository)->remove($orgId + 1, $positionId);

        $this->assertFalse(TestTransactionExecutor::isFlushed($sitePosition));
    }

    private function notificationSent()
    {
        $capNotification = ArgCapture::create();
        $this->notificationService->expects($this->atLeastOnce())->method('add')
            ->with($capNotification());

        return $capNotification;
    }

    private function validSitePosition($siteId)
    {
        $person = new Person();
        $role = new SiteBusinessRole();
        $role->setCode(SiteBusinessRoleCode::TESTER);
        $site = (new Site())->setId($siteId);
        $status = (new BusinessRoleStatus())->setCode(BusinessRoleStatusCode::ACTIVE);
        $map = new SiteBusinessRoleMap();
        $map->setPerson($person);
        $map->setSite($site);
        $map->setSiteBusinessRole($role);
        $map->setBusinessRoleStatus($status);

        return $map;
    }
}
