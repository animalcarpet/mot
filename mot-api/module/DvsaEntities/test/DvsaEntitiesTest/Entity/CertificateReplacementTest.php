<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\CertificateReplacement;

/**
 * Class CertificateReplacementTest.
 */
class CertificateReplacementTest extends BaseEntityTestCase
{
    public function testSetsPropertiesCorrectly()
    {
        $expectedProperties = [
            'motTestVersion',
            'motTest',
            'reasonForDifferentTester',
        ];
        $this->checkGettersAndSetters($expectedProperties, new CertificateReplacement());
    }
}
