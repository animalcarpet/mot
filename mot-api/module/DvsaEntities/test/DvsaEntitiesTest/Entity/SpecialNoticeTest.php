<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\SpecialNotice;

/**
 * Class SpecialNoticeTest.
 */
class SpecialNoticeTest extends BaseEntityTestCase
{
    public function testSetProperites()
    {
        $expectedProperties = [
            'username',
            'content',
        ];
        $this->checkGettersAndSetters($expectedProperties, new SpecialNotice());
    }

    public function testAcknowledgedDateAndFlagIsSetWhenAcknowledged()
    {
        $specialNotice = new SpecialNotice();
        $specialNotice->markAcknowledged();
        $this->assertAttributeInstanceOf(\DateTime::class, 'acknowledgedOn', $specialNotice);
        $this->assertTrue($specialNotice->getIsAcknowledged());
    }
}
