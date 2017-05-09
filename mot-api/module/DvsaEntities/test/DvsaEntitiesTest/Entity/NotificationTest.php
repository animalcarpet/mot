<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\NotificationAction;
use PHPUnit_Framework_TestCase;

/**
 * Class NotificationTest.
 */
class NotificationTest extends PHPUnit_Framework_TestCase
{
    use NotificationCreatorTrait;

    public function testIsActionRequiredShouldReturnFalse()
    {
        $notification = $this->createNotification();

        $this->assertFalse($notification->isActionRequired());
    }

    public function testIsActionRequiredShouldReturnTrue()
    {
        $notification = $this->createNotificationWithTemplateAction();

        $this->assertTrue($notification->isActionRequired());
    }

    public function testIsActionDoneShouldReturnFalse()
    {
        $notification = $this->createNotification();

        $this->assertFalse($notification->isActionDone());
    }

    public function testIsActionDoneShouldReturnTrue()
    {
        $notification = $this->createNotificationWithTemplateAction();
        $notification->setAction(new NotificationAction());

        $this->assertTrue($notification->isActionDone());
    }

    public function testGetFieldValueShouldReturnProperValue()
    {
        $expectedValue = '123';
        $key = 'test';
        $notification = $this->createNotificationWithFields([$key => $expectedValue]);

        $this->assertEquals($expectedValue, $notification->getFieldValue($key));
    }

    public function testGetFieldValueManyFieldsShouldReturnProperValue()
    {
        $notification = $this->createNotificationWithFields(
            [
                'a' => 1,
                'b' => 2,
                'c' => 3,
            ]
        );

        $this->assertEquals(1, $notification->getFieldValue('a'));
        $this->assertEquals(2, $notification->getFieldValue('b'));
        $this->assertEquals(3, $notification->getFieldValue('c'));
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testGetFieldValueShouldThrowBadRequestException()
    {
        $notificationWithoutFields = $this->createNotification();

        $notificationWithoutFields->getFieldValue('there are no fields');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function testGetFieldValueShouldThrowNotFoundException()
    {
        $notification = $this->createNotificationWithFields(['a' => 1]);

        $notification->getFieldValue('this field does not exist');
    }

    public function testIsActionValid()
    {
        $actionAccept = 'MY-ACTION-ACCEPT';
        $actionReject = 'MY-ACTION-REJECT';
        $notification = $this->createNotificationWithTemplateAction([$actionAccept, $actionReject]);

        $this->assertTrue($notification->isActionValid($actionAccept));
        $this->assertTrue($notification->isActionValid($actionReject));
        $this->assertFalse($notification->isActionValid('This action does not exist'));
        $this->assertFalse($notification->isActionValid(strtolower($actionAccept)));
    }
}
