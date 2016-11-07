<?php

namespace DashboardTest\Model;

use Dashboard\Model\Notification;

/**
 * Class NotificationTest
 *
 * @package DashboardTest\Model
 */
class NotificationTest extends \PHPUnit_Framework_TestCase
{
    const ID = 1;
    const CREATED_ON = '2010-10-10 10:10:10';
    const UPDATED_ON = '2010-10-10 10:10:10';
    const READ_ON = '2010-10-10 10:10:10';
    const CONTENT = 'aaa';
    const SUBJECT = 'bbb';

    public function test_getList_emptyArray()
    {
        $result = Notification::createList([]);
        $this->assertCount(0, $result);
    }

    public function test_getList_oneElement()
    {
        $result = Notification::createList([self::getNotificationData()]);
        $this->assertCount(1, $result);
        $this->assertFalse($result[0]->isActionRequired());
    }

    public function test_getList_manyElements()
    {
        $result = Notification::createList([self::getNotificationData(), self::getNominationData()]);
        $this->assertCount(2, $result);
        $this->assertFalse($result[0]->isActionRequired());
        $this->assertTrue($result[1]->isActionRequired());
        $this->assertFalse($result[1]->isDone());
    }

    public function test_notification_shouldBeOk()
    {
        $notification = new Notification(self::getNotificationData());
        $this->assertEquals(self::READ_ON, $notification->getReadOn());
        $this->assertEquals(self::CREATED_ON, $notification->getCreatedOn());
        $this->assertEquals(self::UPDATED_ON, $notification->getUpdatedOn());
        $this->assertEquals(self::CONTENT, $notification->getContent());
        $this->assertEquals(self::SUBJECT, $notification->getSubject());
        $this->assertEquals(self::ID, $notification->getId());
    }

    public function test_nomination_notDone_shouldBeOk()
    {
        $nomination = new Notification(self::getNominationData());
        $this->assertTrue($nomination->isActionRequired());
        $this->assertFalse($nomination->isDone());
        $this->assertCount(2, $nomination->getActions());
    }

    public function test_nomination_rejected_shouldBeOk()
    {
        $nomination = new Notification(self::getNominationData('THIS IS REJECTED NOMINATION'));
        $this->assertTrue($nomination->isDone());
        $this->assertEquals('rejected', $nomination->getFriendlyAction());
    }

    public function test_nomination_accepted_shouldBeOk()
    {
        $nomination = new Notification(self::getNominationData('THIS IS ACCEPTED NOMINATION'));
        $this->assertTrue($nomination->isDone());
        $this->assertEquals('accepted', $nomination->getFriendlyAction());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_expectedInvalidArgumentException()
    {
        new Notification(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function test_expectedUnexpectedValueException()
    {
        $data = self::getNominationData();
        $data['actions'] = false;
        new Notification($data);
    }

    public static function getNotificationData()
    {
        return [
            'id'        => self::ID,
            'createdOn' => self::CREATED_ON,
            'updatedOn' => self::UPDATED_ON,
            'readOn'    => self::READ_ON,
            'content'   => self::CONTENT,
            'subject'   => self::SUBJECT,
            'fields' => [],
            'templateId' => 1
        ];
    }

    public static function getNominationData($action = null)
    {
        $result = self::getNotificationData();
        $result['actions'] = [
            'ACCEPTED' => 'accepted',
            'REJECTED' => 'rejected',
        ];
        $result['action'] = $action;

        return $result;
    }
}
