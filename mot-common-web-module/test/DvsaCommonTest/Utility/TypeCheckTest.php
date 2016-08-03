<?php

namespace DvsaCommonTest\Utility;

use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\Guid\Guid;
use DvsaCommon\Utility\TypeCheck;

class TypeCheckTest extends \PHPUnit_Framework_TestCase
{
    public function testIsPositiveOrZeroInteger()
    {
        $this->assertTrue(TypeCheck::isPositiveInteger(1));
        $this->assertTrue(TypeCheck::isPositiveInteger(0));

        $this->assertFalse(TypeCheck::isPositiveInteger(-1));
        $this->assertFalse(TypeCheck::isPositiveInteger('-1'));
        $this->assertFalse(TypeCheck::isPositiveInteger('string'));
        $this->assertFalse(TypeCheck::isPositiveInteger(new \stdClass));
    }

    public function testIsInteger()
    {
        $this->assertTrue(TypeCheck::isInteger(1));
        $this->assertTrue(TypeCheck::isInteger(0));
        $this->assertTrue(TypeCheck::isInteger(-1));
        $this->assertTrue(TypeCheck::isInteger('-1'));
        $this->assertTrue(TypeCheck::isInteger(-1100000));
        $this->assertTrue(TypeCheck::isInteger('9223372036854775807'));
        $this->assertTrue(TypeCheck::isInteger('-9223372036854775808'));

        $this->assertFalse(TypeCheck::isInteger('string'));
        $this->assertFalse(TypeCheck::isInteger('1337e0'));
        $this->assertFalse(TypeCheck::isInteger('0xFF'));
        $this->assertFalse(TypeCheck::isInteger('9223372036854775808'));
        $this->assertFalse(TypeCheck::isInteger('-9223372036854775809'));
        $this->assertFalse(TypeCheck::isInteger(new \stdClass));
    }

    public function testIsAlphaNumeric()
    {
        $this->assertTrue(TypeCheck::isAlphaNumeric('alphanumeric'));
        $this->assertTrue(TypeCheck::isAlphaNumeric('alphanumericWithNumbers123'));

        $this->assertFalse(TypeCheck::isAlphaNumeric('alphanumericWithNumbers123AndPunctuation$'));
        $this->assertFalse(TypeCheck::isAlphaNumeric('alphanumericWithNumbers123AndPunctuation:!@#$%^&*()_+='));
        $this->assertFalse(TypeCheck::isAlphaNumeric('letters and spaces'));
        $this->assertFalse(TypeCheck::isAlphaNumeric(''));
    }

    public function testAssertArrayShouldNotDoAnything()
    {
        TypeCheck::assertArray([]);
    }

    public function testAssertInstanceShouldNotDoAnything()
    {
        TypeCheck::assertInstance(new \stdClass(), \stdClass::class);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testAssertInstanceShouldThrowRuntimeException()
    {
        TypeCheck::assertInstance(new \stdClass(), self::class);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertArrayBooleanThrowsInvalidArgumentException()
    {
        TypeCheck::assertArray(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertArrayObjectThrowsInvalidArgumentException()
    {
        TypeCheck::assertArray(new \stdClass());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayOfScalarValuesFailsForNull()
    {
        TypeCheck::assertCollectionOfScalarValues(['string', null]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayOfScalarValuesFailsForObject()
    {
        TypeCheck::assertCollectionOfScalarValues(['string', new Guid()]);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testArrayOfScalarValuesFailsForArray()
    {
        TypeCheck::assertCollectionOfScalarValues(['string', ['nested-array']]);
    }
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAssertEnumThrowsExceptionForUnexistingEnumValue()
    {
        TypeCheck::assertEnum('8', VehicleClassCode::class);
    }

    public function testAssertEnumPassesForCorrectEnumValue()
    {
        TypeCheck::assertEnum('7', VehicleClassCode::class);
    }
}
