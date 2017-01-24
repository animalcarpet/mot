<?php


namespace DashboardTest\ViewModel\Notification;


use Dashboard\Controller\NotificationController;
use Dashboard\ViewModel\Notification\NotificationListViewModel;
use DvsaCommonTest\TestUtils\XMock;
use Zend\View\Helper\Url;

class NotificationListViewModelTest extends \PHPUnit_Framework_TestCase
{
    const CLASS_LINK_ACTIVE = 'tab-link--active';
    /** @var  NotificationListViewModel */
    private $vm;

    /** @var  Url|\PHPUnit_Framework_MockObject_MockObject */
    private $urlPlugin;

    public function setUp()
    {
        /** @var Url $urlPlugin */
        $this->urlPlugin = XMock::of(Url::class);
        $this->urlPlugin->expects($this->any())->method('__invoke')->willReturnCallback(function($route){
            return $route;
        });

        $this->vm = new NotificationListViewModel($this->urlPlugin);
    }

    public function testNotificationsAreEmpty()
    {
        $this->vm->setNotifications(null);
        $this->assertEquals(true, $this->vm->notificationsAreEmpty());
        $this->vm->setIsArchiveView(true);
        $this->assertEquals("You don't have any archived notifications", $this->vm->getEmptyNotificationsMessage());

        $this->vm->setNotifications(['asdad']);
        $this->assertEquals(false, $this->vm->notificationsAreEmpty());
        $this->vm->setIsArchiveView(false);
        $this->assertEquals("You don't have any new notifications", $this->vm->getEmptyNotificationsMessage());
    }

    public function testTabRenderingOnArchivePage()
    {
        $unreadCount = 9;
        $this->vm
            ->setIsArchiveView(true)
            ->setUnreadCount($unreadCount);

        $archiveTab = $this->vm->getArchiveTab();
        $inboxTab = $this->vm->getInboxTab();

        $this->assertGreaterThan(0, strpos($inboxTab, "Inbox ({$unreadCount})"));
        $this->assertSame(false, strpos($inboxTab, self::CLASS_LINK_ACTIVE));
        $this->assertGreaterThan(0, strpos($inboxTab, NotificationController::ROUTE_NOTIFICATION_LIST));
        $this->assertGreaterThan(0, strpos($archiveTab, "Archive"));
        $this->assertGreaterThan(0, strpos($archiveTab, self::CLASS_LINK_ACTIVE));
    }

    public function testTabRenderingOnInboxPage()
    {
        $unreadCount = 9;
        $this->vm
            ->setIsArchiveView(false)
            ->setUnreadCount($unreadCount);

        $archiveTab = $this->vm->getArchiveTab();
        $inboxTab = $this->vm->getInboxTab();

        $this->assertGreaterThan(0, strpos($inboxTab, "Inbox ({$unreadCount})"));
        $this->assertGreaterThan(0, strpos($inboxTab, self::CLASS_LINK_ACTIVE));
        $this->assertSame(false, strpos($archiveTab, self::CLASS_LINK_ACTIVE));
        $this->assertGreaterThan(0, strpos($archiveTab, "Archive"));
        $this->assertGreaterThan(0, strpos($archiveTab, NotificationController::ROUTE_NOTIFICATION_ARCHIVE));
    }
}