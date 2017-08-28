<?php

namespace PersonApiTest\Service;

use Dvsa\Mot\AuditApi\Service\HistoryAuditService;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use NotificationApi\Service\NotificationService;
use NotificationApi\Dto\Notification;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\NotificationRepository;
use PersonApi\Service\PasswordExpiryNotificationService;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\PasswordDetailRepository;
use DvsaCommon\Database\Transaction;

class PasswordExpiryNotificationServiceTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_USER_ID = 1010;
    const DEFAULT_USERNAME = 'tester1';

    /** @var NotificationService | \PHPUnit_Framework_MockObject_MockObject */
    private $notificationService;

    /**
     * @var Person
     */
    private $user;

    /**
     * @var PasswordExpiryNotificationService
     */
    private $service;

    /**
     * @var PersonRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $personRepository;

    /**
     * @var HistoryAuditService | \PHPUnit_Framework_MockObject_MockObject
     */
    private $historyAuditService;

    /**
     * @var NotificationRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $notificationRepository;

    /**
     * @var PasswordDetailRepository | \PHPUnit_Framework_MockObject_MockObject
     */
    private $passwordDetailRepository;

    /**
     * @var Transaction | \PHPUnit_Framework_MockObject_MockObject
     */
    private $transaction;

    public function setup()
    {
        $this->withUser(self::DEFAULT_USER_ID);

        $this->notificationService = XMock::of(NotificationService::class);
        $this->personRepository = XMock::of(PersonRepository::class);
        $this->historyAuditService = Xmock::of(HistoryAuditService::class);
        $this->notificationRepository = XMock::of(NotificationRepository::class);

        $this->withPersonRepository();

        $this->passwordDetailRepository = XMock::of(PasswordDetailRepository::class);
        $this->transaction = XMock::of(Transaction::class);

        $this->service = new PasswordExpiryNotificationService(
            $this->notificationService,
            $this->notificationRepository,
            $this->personRepository,
            $this->passwordDetailRepository,
            $this->transaction,
            $this->historyAuditService
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testPasswordExpiryNotificationServiceSendsNotification($day)
    {
        $notificationSpy = new MethodSpy($this->notificationService, 'add');

        $this->service->send($this->user->getId(), $day);

        $this->assertNotification($notificationSpy, Notification::TEMPLATE_PASSWORD_EXPIRY, $day);
    }

    public function dataProvider()
    {
        return [
            [1],
            [2],
            [3],
            [7],
        ];
    }

    /**
     * @dataProvider removeDataProvider
     */
    public function testRemovalOfNotifications($notificationsToRemove)
    {
        $this->withNotificationRepository($notificationsToRemove);
        $this->withHistoryAuditService();

        $this->service->remove(self::DEFAULT_USERNAME);
    }

    public function removeDataProvider()
    {
        $notification = new Notification();

        return [
            [ [] ],
            [ [$notification] ],
            [ [$notification, $notification] ],
            [ [$notification, $notification, $notification] ],
        ];
    }

    private function assertNotification($notificationSpy, $notificationTemplate, $day)
    {
        $this->assertEquals(1, $notificationSpy->invocationCount(),
            "The 'add' method of notification service was not called");

        $notification = $notificationSpy->paramsForLastInvocation()[0];

        $this->assertEquals($notificationTemplate, $notification['template'],
            'Wrong template was chosen for the notification');

        $this->assertEquals($this->user->getId(), $notification['recipient'],
            'It was addressed to the wrong person');

        $this->assertEquals($this->getExpiryDay($day), $notification['fields']['expiryDay'],
            'Wrong expiry day is displayed in the notification');
    }

    private function getExpiryDay($day)
    {
        if ($day === 1) {
            return PasswordExpiryNotificationService::EXPIRY_DAY_TOMORROW;
        }

        return sprintf(PasswordExpiryNotificationService::EXPIRY_IN_XX_DAYS, $day);
    }

    private function withHistoryAuditService()
    {
        $this->historyAuditService
            ->expects($this->once())
            ->method('setUser')
            ->with($this->user);

        $this->historyAuditService
            ->expects($this->once())
            ->method('execute');
    }

    private function withPersonRepository()
    {
        $this->personRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->user);

        $this->personRepository
            ->expects($this->any())
            ->method('getByIdentifier')
            ->willReturn($this->user);
    }

    private function withUser($userId)
    {
        $this->user = new Person();
        $this->user->setId($userId);
    }

    private function withNotificationRepository(array $notificationsToReturn = [])
    {
        $this->notificationRepository
            ->expects($this->any())
            ->method('findAllByTemplateId')
            ->with($this->user->getId(), Notification::TEMPLATE_PASSWORD_EXPIRY)
            ->willReturn($notificationsToReturn);

        $notificationCount = count($notificationsToReturn);
        $shouldFlush = $notificationCount > 0;

        $this->notificationRepository
            ->expects( true === $shouldFlush ? $this->once() : $this->never() )
            ->method('flush');

        $this->notificationRepository
            ->expects($this->exactly($notificationCount))
            ->method('remove');
    }
}
