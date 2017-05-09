<?php

namespace NotificationApiTest\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MockHandler;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\NotificationAction;
use DvsaEntities\Entity\NotificationActionLookup;
use DvsaEntities\Entity\NotificationTemplateAction;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationTemplate;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\NotificationRepository;
use NotificationApi\Service\BusinessLogic\PositionInOrganisationNominationHandler;
use NotificationApi\Service\NotificationService;
use NotificationApi\Service\Validator\NotificationValidator;
use NotificationApiTest\Entity\NotificationCreatorTrait;
use PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount;
use Zend\ServiceManager\ServiceManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;

/**
 * Class NotificationServiceTest.
 *
 * Unit testing NotificationService
 */
class NotificationServiceTest extends AbstractServiceTestCase
{
    use NotificationCreatorTrait;

    /**
     * @var PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $notificationRepositorySpy;

    /** @expectedException \DvsaCommon\Exception\UnauthorisedException */
    public function testGetNotificationThrowsExceptionOnNonAuth()
    {
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(999, 123, false);

        $notificationService = new NotificationService($mocks->serviceManager, $mocks->validator, $mocks->notificationRepository);

        // We are userid: 999 so this should throw an exception
        $notificationService->get(123);
    }

    /** @expectedException \Exception */
    public function testThrowsIfRecipientFailsToLoad()
    {
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(999, null);
        $notificationService = $this->getMockNotificationService($mocks);
        $notificationService->get(123);
    }

    public function testArchivization()
    {
        $notificationId = 999;
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues($notificationId, $notificationId);
        $notificationService = $this->getMockNotificationService($mocks);
        $notificationService->archive($notificationId);
        /** @var \PHPUnit_Framework_MockObject_Invocation_Object $saveInvocation */
        $saveInvocation = $this->notificationRepositorySpy->getInvocations()[0];
        /** @var Notification $notification */
        $notification = $saveInvocation->parameters[0];
        $this->assertEquals(true, $notification->getIsArchived());
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\ForbiddenException */
    public function testArchivizationThrowsExceptionIfUserTriesToModifySomeoneElsesNotification()
    {
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(999, 111);
        $notificationService = $this->getMockNotificationService($mocks);
        $notificationService->archive(222);
    }

    public function test_get_notification_works_when_user_can_see_same_id()
    {
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(42, 42);
        $notificationService = $this->getMockNotificationService($mocks);
        $notification = $notificationService->get(42);
        $this->assertEquals(42, $notification->getId());
    }

    public function test_delete_validId_shouldBeOk()
    {
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(123, 123);
        $notificationService = $this->getMockNotificationService($mocks);

        // We are userid: 999 so this should throw an exception
        $notificationService->get(123);
    }

    /** @expectedException \DvsaCommonApi\Service\Exception\ForbiddenException */
    public function test_delete_invalidId_shouldFail()
    {
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(999, 123);
        $notificationService = $this->getMockNotificationService($mocks);

        // We are userid: 999 so this should throw an exception
        $notificationService->get(123);
    }

    public function test_getAllByUserId_validId_shouldReturnArray()
    {
        $personId = 42;
        /** @var \DvsaEntities\Entity\Person | \PHPUnit_Framework_MockObject_MockObject $mockIdentity */
        $mockIdentity = $this->getMockIdentity($personId);

        /** @var NotificationValidator | \PHPUnit_Framework_MockObject_MockObject $mockValidator */
        $mockValidator = $this->getMockWithDisabledConstructor(NotificationValidator::class);

        /** @var NotificationRepository | \PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = XMock::of(NotificationRepository::class);
        $repository
            ->expects($this->any())
            ->method('findAllByPersonId')
            ->willReturn(['blah blah', 'and more blah']);

        $notificationService = new NotificationService(
            $this->getMockServiceManager($mockIdentity, $personId),
            $mockValidator,
            $repository
        );
        $result = $notificationService->getAllInboxByPersonId($personId);
        $this->assertEquals(['blah blah', 'and more blah'], $result);
    }

    public function test_getUnreadByUserId_validId_shouldReturnArray()
    {
        $personId = 42;
        /** @var \DvsaEntities\Entity\Person | \PHPUnit_Framework_MockObject_MockObject $mockIdentity */
        $mockIdentity = $this->getMockIdentity($personId);

        /** @var NotificationValidator | \PHPUnit_Framework_MockObject_MockObject $mockValidator */
        $mockValidator = $this->getMockWithDisabledConstructor(NotificationValidator::class);

        /** @var NotificationRepository | \PHPUnit_Framework_MockObject_MockObject $repository */
        $repository = XMock::of(NotificationRepository::class);
        $repository
            ->expects($this->any())
            ->method('findUnreadByPersonId')
            ->willReturn(['blah blah', 'and more blah']);

        $notificationService = new NotificationService(
            $this->getMockServiceManager($mockIdentity, $personId),
            $mockValidator,
            $repository
        );
        $result = $notificationService->getUnreadByPersonId($personId);
        $this->assertEquals(['blah blah', 'and more blah'], $result);
    }

    public function test_markAsRead_notificationWasUnread_shouldMarkAsRead()
    {
        $notificationId = 123;
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(123, $notificationId);

        $notificationService = $this->getMockNotificationService($mocks);

        $mocks->notification->setReadOn(null);
        $notificationService->markAsRead($notificationId);
        $readOn = $mocks->notification->getReadOn();
        // Ensure timestamp was set and its at least *now*
        $this->assertNotNull($readOn);
        $this->assertTrue($readOn->getTimestamp() >= time());
    }

    public function test_markAsRead_notificationWasAlreadyRead_shouldNotChangeAnything()
    {
        $notificationId = 123;
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(123, $notificationId);

        $notificationService = $this->getMockNotificationService($mocks);

        $mocks->notification->setReadOn(null);
        $notificationService->markAsRead($notificationId);
        $readOn = $mocks->notification->getReadOn();
        // Ensure timestamp was set and its at least *now*
        $this->assertNotNull($readOn);
        $timestamp1 = $readOn->getTimestamp();
        $this->assertTrue($timestamp1 >= time());

        // Save the original to ensure it is not modified...
        $originalNotification = $mocks->notification;
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(123, $notificationId);
        $mocks->notification = $originalNotification;
        $notificationService = $this->getMockNotificationService($mocks);

        // ensure a second passed to make timestamps stale...
        sleep(1);
        // $n is the returned notification, should be the original
        $n = $notificationService->markAsRead($notificationId);
        $this->assertNotNull($originalNotification->getReadOn());
        $this->assertTrue($timestamp1 <= $n->getReadOn()->getTimestamp());
    }

    public function test_add_validData_shouldReturnNotification()
    {
        /** @var \Zend\ServiceManager\ServiceManager $mockServiceManager */
        $mockServiceManager = $this->getMockWithDisabledConstructor(ServiceManager::class);
        /** @var NotificationValidator $mockValidator */
        $mockValidator = $this->getMockWithDisabledConstructor(NotificationValidator::class);

        // Ensure first call to get() is mocked entity-manager
        $mockEntityManager = $this->getMockEntityManager();
        $mockServiceManager->expects($this->at(0))
            ->method('get')
            ->with(EntityManager::class)
            ->willReturn($mockEntityManager);

        $input = [
            'template' => 1,
            'recipient' => 1,
            'fields' => [
                'key' => 'value',
            ],
        ];

        $notificationService = new NotificationService($mockServiceManager, $mockValidator, XMock::of(NotificationRepository::class));
        /** @var \DvsaEntities\Entity\Person $mockIdentity */
        $mockIdentity = XMock::of('\DvsaEntities\Entity\Person', ['getUserId', 'getId']);

        $mockEntityManager->expects($this->any())
            ->method('find')
            ->will(
                $this->returnCallback(
                    function ($class, $id) use ($mockIdentity) {
                        switch ($class) {
                            case Person::class:
                                $mockIdentity->setId($id);

                                return $mockIdentity;
                            case NotificationTemplate::class:
                                return new NotificationTemplate();
                        }

                        return null;
                    }
                )
            );

        $mockEntityManager->method('persist')
            ->will(
                $this->returnCallback(
                    function ($object) use ($mockIdentity) {
                        switch (get_class($object)) {
                            case Notification::class:
                                // Expected Person carried through?
                                $this->assertEquals($mockIdentity, $object->getRecipient());
                                break;
                        }
                    }
                )
            );
        $notificationService->add($input);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     * @expectedExceptionMessage No action can be taken for this notification
     */
    public function test_action_verifyNotNomination_shouldThrowBadRequestException()
    {
        $notificationId = 123;
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(123, $notificationId);
        /** @var \Zend\ServiceManager\ServiceManager $mockServiceManager */
        $mockServiceManager = $mocks->serviceManager;
        /** @var NotificationValidator $mockValidator */
        $mockValidator = $mocks->validator;

        // Ensure first call to get() is mocked entity-manager
        $mockEntityManager = $this->getMockEntityManager();
        $mockServiceManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($class) use ($mocks) {
                        switch ($class) {
                            case EntityManager::class:
                                return $mocks->entityManager;
                            case \Zend\Authentication\AuthenticationService::class:
                                return $mocks->authService;
                        }

                        return null;
                    }
                )
            );

        $notificationService = new NotificationService($mockServiceManager, $mockValidator, $mocks->notificationRepository);

        $mockEntityManager->expects($this->any())
            ->method('find')
            ->will(
                $this->returnCallback(
                    function ($class, $id) {
                        switch ($class) {
                            case Notification::class:
                                return new Notification();
                        }

                        return null;
                    }
                )
            );

        $notificationService->action(
            $notificationId,
            [
                'action' => PositionInOrganisationNominationHandler::ACCEPTED,
            ]
        );
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function test_action_verifyIllegalAction_shouldThrowBadRequestException()
    {
        $notificationId = 123;
        $mocks = $this->prepSoNotificationGetUsesNotificationAndPersonIdValues(123, $notificationId);
        /** @var \Zend\ServiceManager\ServiceManager $mockServiceManager */
        $mockServiceManager = $mocks->serviceManager;
        /** @var NotificationValidator $mockValidator */
        $mockValidator = $mocks->validator;

        // Ensure first call to get() is mocked entity-manager
        $mockEntityManager = $this->getMockEntityManager();
        $mockServiceManager->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($class) use ($mocks) {
                        switch ($class) {
                            case EntityManager::class:
                                return $mocks->entityManager;
                            case \Zend\Authentication\AuthenticationService::class:
                                return $mocks->authService;
                        }

                        return null;
                    }
                )
            );

        $notificationService = new NotificationService($mockServiceManager, $mockValidator, $mocks->notificationRepository);

        $actionLookup = new NotificationActionLookup();
        $actionLookup->setAction(NotificationActionLookup::ORGANISATION_NOMINATION_ACCEPTED);
        $notificationAction = new NotificationAction();
        $notificationAction->setAction($actionLookup);

        $templateAction = new NotificationTemplateAction();
        $templateAction->setAction($notificationAction);

        $notificationTemplate = new NotificationTemplate();
        $notificationTemplate->setActions([$templateAction]);

        $mocks->notification->setNotificationTemplate($notificationTemplate);

        $notificationService->action(
            $notificationId,
            ['action' => PositionInOrganisationNominationHandler::ACCEPTED]
        );
    }

    /**
     * Given a notification ID and a callback, this function returns an array
     * containing service manager and entity manager mocks. The entity manager
     * will return the notification when "find()" is called, at which time the
     * callback function will be invoked.
     */
    protected function mockEntityManagerForFindNotificationById($id, callable $will)
    {
        $mocks = $this->getMocksForNotificationService();

        $entityMock = new MockHandler($mocks->entityManager, $this);
        $entityMock
            ->find()
            ->with(Notification::class, $id)
            ->will($will());

        return $mocks;
    }

    /**
     * HELPER: returns a new mocked NotificationService using the service
     * manager created by (expected) getMocksForNotificationService.
     *
     * @param $mocks returned from getMocksForNotificationService
     *
     * @return NotificationService
     */
    protected function constructNotificationServiceWithMocks($mocks)
    {
        return new NotificationService(
            $mocks->serviceManager,
            new NotificationValidator(),
            $mocks->notificationRepository
        );
    }

    /**
     * HELPER: Construct a mocked service manager that returns the
     * mocked entity manager on ANY call to "get(EntityManager::class)".
     *
     * @return array CONTAINS mocked service manager "serviceManagerMock" and
     *               mocked entity manager  "entityManagerMock
     */
    protected function getMocksForNotificationService()
    {
        $mockServiceManager = $this->getMockWithDisabledConstructor(\Zend\ServiceManager\ServiceManager::class);
        $mockEntityManager = $this->getMockEntityManager();

        $mockServiceManager
            ->expects($this->at(1))
            ->method('get')
            ->with(EntityManager::class)
            ->will($this->returnValue($mockEntityManager));

        $mockServiceManager
            ->expects($this->at(2))
            ->method('get')
            ->with(DvsaAuthenticationService::class)
            ->will($this->returnValue($mockEntityManager));

        return (object) [
            'serviceManager' => $mockServiceManager,
            'entityManager' => $mockEntityManager,
        ];
    }

    /**
     * Sets a test environment.
     *
     * @param $pid integer the Person->getId() value to mock
     * @param $nid integer the Notification ID value to mock
     *
     * @NOTE NEGATIVE $nid will fail recipient extraction i.e. no mock value returned
     *
     * @return object containing the mocked objects for re-use
     */
    private function prepSoNotificationGetUsesNotificationAndPersonIdValues($pid, $nid, $authorised = true)
    {
        /** @var \Zend\ServiceManager\ServiceManager $mockServiceManager */
        $mockServiceManager = $this->getMockWithDisabledConstructor(ServiceManager::class);
        /** @var NotificationValidator $mockValidator */
        $mockValidator = $this->getMockWithDisabledConstructor(NotificationValidator::class);

        // Ensure first call to get() is mocked entity-manager
        $mockEntityManager = $this->getMockEntityManager();

        // *This* Person is the USER SESSION (identity) entity
        /** @var \DvsaEntities\Entity\Person $mockIdentity */
        $mockIdentity = XMock::of('\DvsaEntities\Entity\Person', ['getUserId', 'getId']);

        // Authentications service answers: $mockIdentity, the Person in the session
        $mockAuthService = XMock::of('\Zend\Authentication\AuthenticationService');

        // *This* Person is the Notification.recipient entity
        /** @var \DvsaEntities\Entity\Person $mockRecipient */
        $dataNotification = new Notification();

        // IFF we have a notification ID then we need to set the way for a few calls
        // to be made to the underlying repository etc.
        if ($nid && $authorised === true) {
            $mockRecipient = XMock::of('\DvsaEntities\Entity\Person', ['getUserId', 'getId']);
            $mockRecipient->expects($this->once())->method('getId')->willReturn($nid);

            // Create the actual Notification object for validation by user id..
            $dataNotification = new Notification();
            $dataNotification->setId($nid);
            $dataNotification->setRecipient($mockRecipient);

            // ... getting the user id to see if they match
            $mockIdentity->expects($this->once())->method('getUserId')->willReturn($pid);

            // ... getting the notification recipient id to be matched with the above user...
            $mockAuthService->expects($this->once())
                ->method('getIdentity')
                ->willReturn($mockIdentity);
        }

        $mockAuthorisationService = $this->mockAuthorisation($authorised);

        $mockServiceManager->expects($this->any())
            ->method('get')
            ->willReturnCallback(function ($serviceName) use ($mockEntityManager, $mockAuthService, $mockAuthorisationService) {
                switch ($serviceName) {
                        case EntityManager::class:
                            return $mockEntityManager;
                        case 'DvsaAuthenticationService':
                            return $mockAuthService;
                        case 'DvsaAuthorisationService':
                            return $mockAuthorisationService;
                        default:
                            return null;
                    }
            }
            );

        $notificationRepository = XMock::of(NotificationRepository::class);
        $this->notificationRepositorySpy = $this->any();

        $notificationRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($dataNotification);

        $notificationRepository->expects($this->notificationRepositorySpy)->method('save');

        return (object) [
            'serviceManager' => $mockServiceManager,
            'entityManager' => $mockEntityManager,
            'validator' => $mockValidator,
            'authService' => $mockAuthService,
            'person' => $mockIdentity,
            'notification' => $dataNotification,
            'notificationRepository' => $notificationRepository,
        ];
    }

    private function mockAuthorisation($authorised = true)
    {
        $mockAuthorisationService = XMock::of(AuthorisationServiceInterface::class, ['assertGranted']);

        if ($authorised === true) {
            $mockAuthorisationService->expects($this->any())
                ->method('assertGranted')
                ->willReturn(true);
        } else {
            $mockAuthorisationService->expects($this->any())
                ->method('assertGranted')
                ->willThrowException(new \DvsaCommon\Exception\UnauthorisedException('blah'));
        }

        return $mockAuthorisationService;
    }

    private function getMockNotificationService($mocks)
    {
        return new NotificationService($mocks->serviceManager, $mocks->validator, $mocks->notificationRepository);
    }

    private function getMockServiceManager($mockIdentity, $personId)
    {
        /** @var \Zend\ServiceManager\ServiceManager | \PHPUnit_Framework_MockObject_MockObject $mockServiceManager */
        $mockServiceManager = $this->getMockWithDisabledConstructor(ServiceManager::class);

        // Ensure first call to get() is mocked entity-manager
        $mockEntityManager = $this->getMockEntityManager();
        $mockEntityManager
            ->expects($this->at(0))
            ->method('find')
            ->with(Person::class, $personId)
            ->willReturn($mockIdentity);
        $mockServiceManager
            ->expects($this->at(0))
            ->method('get')
            ->with(EntityManager::class)
            ->willReturn($mockEntityManager);

        $authorisation = $this->mockAuthorisation();

        $mockServiceManager
            ->expects($this->at(1))
            ->method('get')
            ->willReturn($authorisation);

        return $mockServiceManager;
    }

    private function getMockIdentity($personId)
    {
        $mockIdentity = XMock::of(Person::class, ['getUserId', 'getId']);

        return $mockIdentity;
    }
}
