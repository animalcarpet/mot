<?php

namespace DvsaMotApiTest\Helper;

use DvsaCommon\Constants\Role;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use NotificationApi\Service\NotificationService;
use NotificationApi\Dto\Notification;
use DvsaEntities\Entity\Person;
use DvsaMotApi\Helper\RoleNotificationHelper;
use DvsaEntities\Entity\PersonSystemRole;

class RoleNotificationHelperTest extends \PHPUnit_Framework_TestCase
{
    /** @var NotificationService | \PHPUnit_Framework_MockObject_MockObject */
    private $notificationService;

    /**
     * @var Person
     */
    private $user;

    /**
     * @var RoleNotificationHelper
     */
    private $helper;

    /**
     * @var PersonSystemRole
     */
    private $role;

    public function setup()
    {
        $this->user = new Person();
        $this->user->setId(1010);

        $this->notificationService = XMock::of(NotificationService::class);

        $this->helper = new RoleNotificationHelper($this->notificationService);

        $role = new PersonSystemRole();
        $role->setFullName('DVSA Area Admin');
        $this->role = $role;
    }

    public function testRoleNotificationHelperSendsRemoveRoleNotification()
    {
        $notificationSpy = new MethodSpy($this->notificationService, 'add');

        $this->helper->sendRemoveRoleNotification($this->user, $this->role);

        $this->assertNotification($notificationSpy, Notification::TEMPLATE_DVSA_REMOVE_ROLE);
    }

    public function testRoleNotificationHelperSendsAssignRoleNotification()
    {
        $notificationSpy = new MethodSpy($this->notificationService, 'add');

        $this->helper->sendAssignRoleNotification($this->user, $this->role);

        $this->assertNotification($notificationSpy, Notification::TEMPLATE_DVSA_ASSIGN_ROLE);
    }

    private function assertNotification($notificationSpy, $notificationTemplate)
    {
        $this->assertEquals(1, $notificationSpy->invocationCount(),
            "The 'add' method of notification service was not called");

        $notification = $notificationSpy->paramsForLastInvocation()[0];

        $this->assertEquals($notificationTemplate, $notification['template'],
            'Wrong template was chosen for the notification');

        $this->assertEquals($this->user->getId(), $notification['recipient'],
            'It was addressed to the wrong person');

        $this->assertEquals($this->role->getFullName(), $notification['fields']['role'],
            'Wrong role name is displayed in the notification');
    }
}
