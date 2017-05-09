<?php

namespace DashboardTest\Action;

use Core\Action\ViewActionResult;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dashboard\Action\NotificationAction;
use Dashboard\Data\ApiNotificationResource;
use Dashboard\ViewModel\Notification\NotificationListViewModel;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Zend\View\Helper\Url;

class NotificationActionTest extends PHPUnit_Framework_TestCase
{
    const USER_ID = 1;
    const UNREAD_COUNT = 4;
    /** @var MotFrontendIdentityProviderInterface| \PHPUnit_Framework_MockObject_MockObject */
    private $frontendIdentityProvider;
    /** @var ApiNotificationResource | \PHPUnit_Framework_MockObject_MockObject */
    private $notificationResource;
    /** @var Url | \PHPUnit_Framework_MockObject_MockObject */
    private $url;
    /** @var NotificationAction */
    private $action;

    public function setUp()
    {
        $this->frontendIdentityProvider = XMock::of(MotFrontendIdentityProviderInterface::class);
        $this->frontendIdentityProvider
            ->expects($this->once())
            ->method('getIdentity')
            ->willReturn((new Identity())->setUserId(self::USER_ID));

        $this->notificationResource = XMock::of(ApiNotificationResource::class);
        $this->notificationResource
            ->expects($this->once())
            ->method('getUnreadCount')
            ->willReturnCallback(function ($personId) {
                $this->assertEquals(self::USER_ID, $personId);

                return self::UNREAD_COUNT;
            });

        $this->url = XMock::of(Url::class);

        $this->action = new NotificationAction(
            $this->url,
            $this->notificationResource,
            $this->frontendIdentityProvider
        );
    }

    public function testArchiveAction()
    {
        $this->notificationResource
            ->expects($this->once())
            ->method('getArchivedNotifications')
            ->willReturnCallback(function ($personId) {
                $this->assertEquals(self::USER_ID, $personId);

                return $this->getNotifications();
            });

        $actionResult = $this->action->getArchiveView();
        /** @var NotificationListViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();
        $this->assertEquals(true, $viewModel->isArchiveView());
        $this->assertCommonThingsAreOk($actionResult);
    }

    public function testInboxAction()
    {
        $this->notificationResource
            ->expects($this->once())
            ->method('getInboxNotifications')
            ->willReturnCallback(function ($personId) {
                $this->assertEquals(self::USER_ID, $personId);

                return $this->getNotifications();
            });

        $actionResult = $this->action->getInboxView();
        /** @var NotificationListViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();
        $this->assertEquals(false, $viewModel->isArchiveView());
        $this->assertCommonThingsAreOk($actionResult);
    }

    private function assertCommonThingsAreOk(ViewActionResult $actionResult)
    {
        /** @var NotificationListViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();
        $this->assertEquals('Notifications', $actionResult->layout()->getPageTitle());
        $this->assertArrayHasKey('Notifications', $actionResult->layout()->getBreadcrumbs());
        $this->assertEquals(self::UNREAD_COUNT, $viewModel->getUnreadCount());
        $this->assertCount(count($this->getNotifications()), $viewModel->getNotifications());
    }

    private function getNotifications()
    {
        $data = [
            'id' => 1,
            'recipientId' => 1,
            'templateId' => 20,
            'subject' => 'Qualified => Tester Status change',
            'content' => 'Your tester qualification status for group B has been changed from Qualified to Qualified.',
            'readOn' => '2017-01-09',
            'createdOn' => '2017-01-09',
            'isArchived' => false,
            'fields' => [
                'group' => 'B',
                'previousStatus' => 'Qualified',
                'newStatus' => 'Qualified',
            ],
            'updatedOn' => '',
        ];

        return [
            $data,
            $data,
        ];
    }
}
