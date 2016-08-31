<?php

namespace DvsaCommon\PHPUnit;

use DvsaCommon\Date\TimeSpan;
use DvsaCommon\PHPUnit\Constraint\TimeSpanEqualsConstraint;

class AbstractMotUnitTest extends \PHPUnit_Framework_TestCase
{
    public static function assertTimeSpanEquals(TimeSpan $expected, TimeSpan $actual, $message = '')
    {
        $constraint = new TimeSpanEqualsConstraint($expected);

        self::assertThat($actual, $constraint, $message);
    }
}
