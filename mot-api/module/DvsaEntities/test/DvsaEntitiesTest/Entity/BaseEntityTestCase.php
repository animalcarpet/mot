<?php

namespace DvsaEntitiesTest\Entity;

use PHPUnit_Framework_TestCase;

/**
 * Class BaseEntityTestCase.
 */
class BaseEntityTestCase extends PHPUnit_Framework_TestCase
{
    public function checkGettersAndSetters($fields, $entity)
    {
        $number = 0;
        foreach ($fields as $expectedProperty) {
            $propertyCapitalName = ucfirst($expectedProperty);
            $setterName = 'set'.$propertyCapitalName;
            $getterName = 'get'.$propertyCapitalName;
            $setterReturnValue = $entity->$setterName('testValue'.$number);
            $this->assertSame('testValue'.$number, $entity->$getterName());
            $this->assertSame($entity, $setterReturnValue);
            ++$number;
        }
    }
}
