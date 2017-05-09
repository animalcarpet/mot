<?php

namespace PersonApiTest\Service;

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

    public function setup()
    {
        $this->user = new Person();
        $this->user->setId(1010);

        $this->notificationService = XMock::of(NotificationService::class);

        $personRepository = XMock::of(PersonRepository::class);
        $personRepository
            ->expects($this->any())
            ->method('get')
            ->willReturn($this->user);

        $this->service = new PasswordExpiryNotificationService(
            $this->notificationService,
            XMock::of(NotificationRepository::class),
            $personRepository,
            XMock::of(PasswordDetailRepository::class),
            XMock::of(Transaction::class)
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
}
