<?php

namespace DashboardTest\ViewModel;

use Dashboard\Model\Notification;
use Dashboard\ViewModel\NotificationViewModel;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\Mvc\Controller\Plugin\Url;

class NotificationViewModelTest extends PHPUnit_Framework_TestCase
{
    public function testLinkAndDateAreCreatedCorrectly()
    {
        $notification = $this->buildNotification();

        $url = XMock::of(Url::class);
        $url
            ->expects($urlSpy = $this->any())
            ->method('fromRoute');

        $viewModel = NotificationViewModel::fromNotification($notification, $url);

        $this->assertEquals($notification->getSubject(), $viewModel->getLinkViewModel()->getText());
        $this->assertEquals(1, $urlSpy->getInvocationCount());
        $this->assertEquals('10 January 2017, 3:00pm', $viewModel->getCreatedOn());
    }

    public function testNotificationIsReadIfUpdatedOnIsAValidDate()
    {
        $notification = $this->buildNotification(['readOn' => '2017-01-11T15:00:11Z']);

        $url = XMock::of(Url::class);

        $viewModel = NotificationViewModel::fromNotification($notification, $url);

        $this->assertFalse($viewModel->isUnread());
    }

    public function testNotificationIsUnreadIfUpdatedOnIsNotAValidDate()
    {
        $notification = $this->buildNotification(['readOn' => null]);

        $url = XMock::of(Url::class);

        $viewModel = NotificationViewModel::fromNotification($notification, $url);

        $this->assertTrue($viewModel->isUnread());
    }

    public function cssClassCombinations()
    {
        return [
            ['is-unread', ['readOn' => null]],
            ['is-read', ['readOn' => '2017-01-11T15:00:11Z']],
            ['is-unread is-nomination', ['readOn' => null, 'actions' => ['REJECTED'], 'action' => 'REJECTED']],
            ['is-read is-nomination', ['readOn' => '2017-01-11T15:00:11Z', 'actions' => ['REJECTED'], 'action' => 'REJECTED']],
        ];
    }

    /**
     * @dataProvider cssClassCombinations
     *
     * @param $expectedCssClass
     * @param $notificationData
     *
     * @throws \Exception
     */
    public function testCssClass($expectedCssClass, $notificationData)
    {
        $notification = $this->buildNotification($notificationData);

        $url = XMock::of(Url::class);

        $viewModel = NotificationViewModel::fromNotification($notification, $url);

        $this->assertEquals($expectedCssClass, $viewModel->getCssClass());
    }

    /**
     * @param array $data
     *
     * @return Notification
     */
    private function buildNotification(array $data = [])
    {
        $defaults = [
            'id' => 7,
            'subject' => 'A test notification',
            'content' => 'Lorem ipsum...',
            'createdOn' => '2017-01-10T15:00:11Z',
            'updatedOn' => '',
            'fields' => [],
            'templateId' => 9,
            'isArchived' => false,
        ];

        return new Notification(array_merge($defaults, $data));
    }
}
