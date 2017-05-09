<?php

namespace DvsaMotTest\Model;

use PHPUnit_Framework_TestCase;

/**
 * Class SpecialNoticeTest.
 */
class SpecialNoticeTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $specialNotice = new SpecialNotice();

        $this->assertNull(
            $specialNotice->noticeTitle,
            '"noticeTitle" should initially be null'
        );
        $this->assertNull(
            $specialNotice->internalPublishDate,
            '"internalPublishDateDay" should initially be null'
        );
        $this->assertNull(
            $specialNotice->externalPublishDate,
            '"externalPublishDateDay" should initially be null'
        );
        $this->assertNull(
            $specialNotice->acknowledgementPeriod,
            '"acknowledgementPeriod" should initially be null'
        );
        $this->assertNull(
            $specialNotice->noticeText,
            '"messageBody" should initially be null'
        );
    }

    public function testExchangeArraySetsPropertiesCorrectly()
    {
        $sn = new SpecialNotice();
        $data = [
            'title' => 'test title',
            'internalPublishDate' => '2014-10-11',
            'externalPublishDate' => '2015-12-13',
            'expiryDate' => '2015-12-14',
            'targetRoles' => ['TESTER-CLASS-1', 'TESTER-CLASS-2'],
            'noticeText' => 'This is the body of the message',
        ];

        $sn->exchangeArray($data);

        $this->assertEquals($data['title'], $sn->noticeTitle);
        $this->assertEquals($data['noticeText'], $sn->noticeText);
        $this->assertEquals($data['internalPublishDate'], $sn->internalPublishDate);
        $this->assertEquals($data['externalPublishDate'], $sn->externalPublishDate);

        // number of day between external publish and expiry
        $this->assertEquals(1, $sn->acknowledgementPeriod);
    }
}
