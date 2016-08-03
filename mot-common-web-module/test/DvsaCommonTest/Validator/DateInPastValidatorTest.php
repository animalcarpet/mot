<?php

namespace DvsaCommonTest\Validator;

use DvsaCommon\Validator\DateInPastValidator;

class DateInPastValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DateInPastValidator $validator */
    private $validator;

    public function setUp()
    {
        $this->validator = new DateInPastValidator();
    }

    public function testIsEmpty_shouldFail()
    {
        $this->assertFalse($this->validator->isValid([]));
        $this->assertFalse($this->validator->isValid(""));
    }

    /**
     * @dataProvider invalidDateFormat
     */
    public function testNotInvalidFormat_shouldFail($date)
    {
        $this->assertFalse($this->validator->isValid($date));
    }

    public function invalidDateFormat()
    {
        return [
            ["2016-13-01"],
            ["2015-December-01"],
            ["2016 12 14"],
            ["1 December 2015"],
            ["first day of previous month"],
            ["01-01-2016"],
            ["2015"],
        ];
    }

    public function testValidDate_shouldPass()
    {
        $nowDate = new \DateTIme('-1 day');
        $this->assertTrue(
            $this->validator->isValid($this->dateToString($nowDate))
        );

    }

    public function testValidNowDate_shouldPass()
    {
        $nowDate = new \DateTIme('now');
        $this->assertTrue(
            $this->validator->isValid($this->dateToString($nowDate))
        );
    }

    public function testFutureDate_shouldFail()
    {
        $futureDate = new \DateTime('+1 day');
        $this->assertFalse(
            $this->validator->isValid($this->dateToString($futureDate))
        );
    }

    protected function dateToString(\DateTime $date)
    {
        return $date->format('Y-m-d');
    }
}