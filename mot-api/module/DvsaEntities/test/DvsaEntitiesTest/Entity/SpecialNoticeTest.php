<?php
namespace DvsaEntitiesTest\Entity;

use DvsaEntitiesTest\Entity\BaseEntityTestCase;
use DvsaEntities\Entity\SpecialNotice;

/**
 * Class SpecialNoticeTest
 *
 * @package DvsaEntitiesTest\Entity
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
