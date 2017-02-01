<?php

namespace DashboardTest\ViewModel;

use Dashboard\ViewModel\LinkViewModel;
use Dashboard\ViewModel\NotificationsViewModel;
use Dashboard\ViewModel\NotificationViewModel;
use PHPUnit_Framework_TestCase;

class NotificationsViewModelTest extends PHPUnit_Framework_TestCase
{
    const RANDOM_DATE_STRING = '2017-01-11T15:00:11Z';

    public function testNotificationsAreIterable()
    {
        $notificationsViewModel = new NotificationsViewModel([
            $this->buildReadNotificationViewModel(),
            $this->buildReadNotificationViewModel()
        ]);
        
        foreach ($notificationsViewModel as $notificationViewModel) {
            $this->assertInstanceOf(NotificationViewModel::class, $notificationViewModel);
        }
    }

    public function testNotificationsSumUnreadItems()
    {
        $notificationsViewModel = new NotificationsViewModel([
            $this->buildUnreadNotificationViewModel(),
            $this->buildReadNotificationViewModel(),
            $this->buildUnreadNotificationViewModel()
        ]);

        $this->assertEquals(2, $notificationsViewModel->countUnread());
    }

    /**
     * @return NotificationViewModel
     */
    private function buildUnreadNotificationViewModel()
    {
        return new NotificationViewModel(new LinkViewModel('Text', '/href'), self::RANDOM_DATE_STRING, true);
    }

    /**
     * @return NotificationViewModel
     */
    private function buildReadNotificationViewModel()
    {
        return new NotificationViewModel(new LinkViewModel('Text', '/href'), self::RANDOM_DATE_STRING, false);
    }
}
