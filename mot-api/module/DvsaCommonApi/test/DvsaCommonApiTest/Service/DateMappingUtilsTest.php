<?php

namespace DvsaCommonApiTest\Service;

use PHPUnit_Framework_TestCase;
use DvsaCommonApi\Service\DateMappingUtils;

/**
 * Class DateMappingUtilsTest.
 */
class DateMappingUtilsTest extends PHPUnit_Framework_TestCase
{
    public function testExtractDateTimeOrUnsetField_set()
    {
        $data = ['someOtherField' => 'whatevs', 'dateThatIsSet' => new \DateTime()];

        DateMappingUtils::extractDateTimeOrUnsetField($data, 'dateThatIsSet');

        $this->assertInternalType('string', $data['dateThatIsSet']);
    }

    public function testExtractDateTimeOrUnsetField_setButNull()
    {
        $data = ['someOtherField' => 'whatevs', 'emptyDate' => null];

        DateMappingUtils::extractDateTimeOrUnsetField($data, 'emptyDate');

        $this->assertFalse(array_key_exists('emptyDate', $data));
    }

    public function testExtractDateTimeOrUnsetField_unset()
    {
        $data = ['someOtherField' => 'whatevs'];

        DateMappingUtils::extractDateTimeOrUnsetField($data, 'emptyDate');

        $this->assertFalse(array_key_exists('emptyDate', $data));
    }

    public function testExtractDateObject_null()
    {
        $result = DateMappingUtils::extractDateObject(null);
        $this->assertNull($result);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExtractDateObject_incorrectClass()
    {
        $result = DateMappingUtils::extractDateObject(new DateMappingUtils());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExtractDateObject_string()
    {
        $result = DateMappingUtils::extractDateObject('1999-09-09');
    }

    public function testExtractDateObject_date()
    {
        $result = DateMappingUtils::extractDateObject(new \DateTime());
        $this->assertInternalType('string', $result);
        // Expect time to have been stripped
        $this->assertEquals(strlen('yyyy-mm-dd'), strlen($result));
    }

    public function testExtractDateTimeObject_date()
    {
        $result = DateMappingUtils::extractDateTimeObject(new \DateTime());
        $this->assertInternalType('string', $result);
        // Expect time to be present
        $this->assertEquals(strlen('yyyy-mm-ddThh:mm:ssZ'), strlen($result));
    }
}
