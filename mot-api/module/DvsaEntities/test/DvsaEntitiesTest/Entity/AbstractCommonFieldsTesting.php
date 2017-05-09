<?php

namespace DvsaEntitiesTest\Entity;

/**
 * Class AbstractCommonFieldsTesting.
 */
abstract class AbstractCommonFieldsTesting
{
    public static function checkInitStateOfCommonFields($that, $entity)
    {
        //        $that->assertNotNull($entity->getCreatedBy(), '"createdBy" should initially be null');
//        $that->assertNotNull($entity->getCreatedOn(), '"createdOn" should initially be null');
        $that->assertNull($entity->getLastUpdatedBy(), '"lastUpdatedBy" should initially be null');
        $that->assertNull($entity->getLastUpdatedOn(), '"lastUpdatedOn" should initially be null');
        $that->assertEquals(1, $entity->getVersion(), '"version" should initially be 1');
    }
}
