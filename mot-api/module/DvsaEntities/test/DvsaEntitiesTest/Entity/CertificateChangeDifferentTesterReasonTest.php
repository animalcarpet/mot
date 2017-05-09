<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;

/**
 * Class CertificateChangeDifferentTesterReasonTest.
 */
class CertificateChangeDifferentTesterReasonTest extends BaseEntityTestCase
{
    public function testSetsPropertiesCorrectly()
    {
        $expectedProperties = [
            'description',
        ];
        $this->checkGettersAndSetters($expectedProperties, new CertificateChangeDifferentTesterReason());
    }
}
