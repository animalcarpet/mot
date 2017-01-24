<?php

namespace DvsaCommonTest\UrlBuilder;

use DvsaCommon\UrlBuilder\NotificationUrlBuilder;
use PHPUnit_Framework_TestCase;

/**
 * Class NotificationUrlBuilderTest
 *
 * @package DvsaCommonTest\UrlBuilder
 */
class NotificationUrlBuilderTest extends PHPUnit_Framework_TestCase
{
    private $url;

    public function setUp()
    {
        $this->url = 'notification/';
    }

    public function test_notification_withId_shouldBeOk()
    {
        $this->assertSame($this->url . '1', NotificationUrlBuilder::notification(1)->toString());
    }

    public function test_notification_read_shouldBeOk()
    {
        $this->assertSame($this->url . '1/read', NotificationUrlBuilder::notification(1)->read()->toString());
    }

    public function test_notification_action_shouldBeOk()
    {
        $this->assertSame($this->url . '1/action', NotificationUrlBuilder::notification(1)->action()->toString());
    }

    public function test_notification_archive_shouldBeOk()
    {
        $this->assertSame($this->url . '1/archive', NotificationUrlBuilder::notification(1)->archive()->toString());
    }

    public function test_notificationForPerson_withId_shouldBeOk()
    {
        $this->assertSame($this->getUrlForPerson(1), $this->createUrlBuilder(1));
    }

    public function test_notificationUnreadCount_withId_shouldBeOk()
    {
        $this->assertSame(
            $this->url . 'person/1/unread-count',
            NotificationUrlBuilder::unreadNotificationsCountForPerson(1)->toString()
        );
    }

    public function test_notificationForPerson_read_shouldBeOk()
    {
        $this->assertSame(
            $this->getUrlForPerson(1, 'read'),
            $this->createUrlBuilder(1, 'read')
        );
    }

    private function getUrlForPerson($id, $action = '')
    {
        $url = $this->url . 'person/' . $id;

        if ($action) {
            $url .= '/' . $action;
        }

        return $url;
    }

    /**
     * @param $id
     * @param $action
     *
     * @return NotificationUrlBuilder
     */
    private function createUrlBuilder($id, $action = '')
    {
        $obj = NotificationUrlBuilder::notificationForPerson();

        $obj->routeParam('personId', $id);

        if ($action) {
            $obj->$action();
        }

        return $obj->toString();
    }
}
