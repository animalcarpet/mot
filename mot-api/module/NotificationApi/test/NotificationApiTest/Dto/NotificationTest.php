<?php

namespace NotificationApiTest\Dto;

use NotificationApi\Dto\Notification;

/**
 * Class NotificationTest.
 */
class NotificationTest extends \PHPUnit_Framework_TestCase
{
    public function testToArrayOneFieldShouldReturnCorrectArray()
    {
        $this->runTestToArrayFieldList(['test1' => 1]);
    }

    public function testToArrayTwoFieldsShouldReturnCorrectArray()
    {
        $this->runTestToArrayFieldList(['test1' => 1, 'test2' => 2]);
    }

    public function testToArraySetFieldsShouldReturnCorrectArray()
    {
        $fields = ['test1' => 1, 'test2' => 2];
        $notification = $this->createNotification();
        $notification->setFields($fields);
        $this->runAssertion($notification, $fields);
    }

    private function runTestToArrayFieldList($fields)
    {
        $notification = $this->createNotification();

        foreach ($fields as $key => $value) {
            $notification->addField($key, $value);
        }
        $this->runAssertion($notification, $fields);
    }

    private function runAssertion(Notification $notification, $addedFields)
    {
        $result = $notification->toArray();
        $this->assertWellFormedData($result);
        $this->assertCount(count($addedFields), $result['fields']);
    }

    private function createNotification()
    {
        $notification = new Notification();
        $notification
            ->setTemplate(Notification::TEMPLATE_ORGANISATION_NOMINATION)
            ->setRecipient(1);

        return $notification;
    }

    private function assertWellFormedData($data)
    {
        $this->assertTrue(
            is_array($data)
            && isset($data['template'])
            && isset($data['recipient'])
            && isset($data['fields'])
            && is_array($data['fields'])
        );
    }
}
