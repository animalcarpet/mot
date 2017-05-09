<?php

namespace DvsaClientTest\Entity;

use DvsaClient\Entity\Phone;

/**
 * Class PhoneTest.
 */
class PhoneTest extends BaseEntityTestCase
{
    public function testSetProperties()
    {
        $expectedProperties = [
            'id',
            'contactType',
            'number',
            'isPrimary',
        ];
        $this->checkGettersAndSetters($expectedProperties, new Phone());
    }
}
