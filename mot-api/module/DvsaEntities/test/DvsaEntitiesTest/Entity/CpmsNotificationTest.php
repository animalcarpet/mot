<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace DvsaEntitiesTest\Entity;

use DateTime;
use DvsaEntities\Entity\CpmsNotification;
use DvsaEntities\Entity\CpmsNotificationScope;
use DvsaEntities\Entity\CpmsNotificationStatus;
use DvsaEntities\Entity\CpmsNotificationType;
use PHPUnit_Framework_TestCase;

class CpmsNotificationTest extends PHPUnit_Framework_TestCase
{
    public function testIntialState()
    {
        $cpmsNotification = new CpmsNotification();

        $this->assertNull($cpmsNotification->getId());
        $this->assertNull($cpmsNotification->getNotificationId());
        $this->assertNull($cpmsNotification->getNotificationType());
        $this->assertNull($cpmsNotification->getScope());
        $this->assertNull($cpmsNotification->getStatus());
        $this->assertNull($cpmsNotification->getReceiptReference());
        $this->assertNull($cpmsNotification->getMandateReference());
        $this->assertNull($cpmsNotification->getRawNotification());
        $this->assertNull($cpmsNotification->getCreatedOn());
        $this->assertNull($cpmsNotification->getLastUpdatedOn());
    }

    public function testSetCpmsNotificationFieldsCorrectly()
    {
        $data = [
            'id' => '12345',
            'notificationId' => 'asdsad',
            'notificationType' => (new CpmsNotificationType())->setCode(CpmsNotificationType::PAYMENT_CODE),
            'scope' => (new CpmsNotificationScope())->setCode('CARD'),
            'status' => (new CpmsNotificationStatus())->setCode(CpmsNotificationType::PAYMENT_CODE),
            'receiptReference' => 'MOT2-12-12345678-123456-12345678',
            'mandateReference' => 'MOT2-12-12345678-123456-87654321',
            'rawNotification' => 'PAYMENT-NOTIFICATION-v1
                                             {
                                                "origin": "CPMS/PAYMENT-SERVICE",
                                                "notification_id":' .'"'.rand(0, 20000).'"'.'
                                                "acknowledge_by": "2016-11-03 10:14:45",
                                                "scheme": "MOT2",
                                                "scope": "CARD",
                                                "event_type": "complete",
                                                "event_cause": "confirmed",
                                                "event_date": "2016-11-03 09:44:45",
                                                "last_sent": "2016-11-03 09:45:00",
                                                "sent_attempts": 1,
                                                "receipt_reference": "123456",
                                                "amount": 75.00
                                             }',
            'createdOn' => new DateTime('2015-01-01'),
            'lastUpdatedOn' => new DateTime('2015-01-02'),
        ];

        $cpmsNotification = new CpmsNotification();

        $cpmsNotification->setId($data['id']);
        $cpmsNotification->setNotificationId($data['notificationId']);
        $cpmsNotification->setNotificationType($data['notificationType']);
        $cpmsNotification->setScope($data['scope']);
        $cpmsNotification->setStatus($data['status']);
        $cpmsNotification->setReceiptReference($data['receiptReference']);
        $cpmsNotification->setMandateReference($data['mandateReference']);
        $cpmsNotification->setRawNotification($data['rawNotification']);
        $cpmsNotification->setCreatedOn($data['createdOn']);
        $cpmsNotification->setLastUpdatedOn($data['lastUpdatedOn']);

        $this->assertEquals($data['id'], $cpmsNotification->getId());
        $this->assertEquals($data['notificationId'], $cpmsNotification->getNotificationId());
        $this->assertEquals($data['notificationType'], $cpmsNotification->getNotificationType());
        $this->assertEquals($data['scope'], $cpmsNotification->getScope());
        $this->assertEquals($data['status'], $cpmsNotification->getStatus());
        $this->assertEquals($data['receiptReference'], $cpmsNotification->getReceiptReference());
        $this->assertEquals($data['mandateReference'], $cpmsNotification->getMandateReference());
        $this->assertEquals($data['rawNotification'], $cpmsNotification->getRawNotification());
        $this->assertEquals($data['createdOn'], $cpmsNotification->getCreatedOn());
        $this->assertEquals($data['lastUpdatedOn'], $cpmsNotification->getLastUpdatedOn());
    }
}
