<?php

namespace DvsaCommonTest\Validator;

use DvsaCommon\Validator\BetweenDatesValidator;
use DvsaCommon\Date\DateTimeApiFormat;

class BetweenDatesValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidatorThrowsExceptionWhenMinDateIsGreaterThanMaxDate()
    {
        $this->createValidator(new \DateTime("2016-04-21"), new \DateTime("2016-02-15"));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testValidatorThrowsExceptionWhenMinDateIsEqualMaxDate()
    {
        $this->createValidator(new \DateTime("2016-04-21"), new \DateTime("2016-04-21"));
    }

    /**
     * @dataProvider getInvalidDate
     */
    public function testIsValidReturnsFalseForInvalidDateForNotInclusiveRange($date, \DateTime $minDate, \DateTime $maxDate)
    {
        $format = DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY;

        $expectedMessages = [
            BetweenDatesValidator::INVALID_DATE => str_replace(["%minDate%", "%maxDate%"], [$minDate->format($format), $maxDate->format($format)], BetweenDatesValidator::ERR_MSG_INVALID_DATE)
        ];

        $this->assertIsValidReturnsFalse($date, $minDate, $maxDate, false, $expectedMessages);
    }

    public function getInvalidDate()
    {
        return [
            [
                $this->getMinDate(),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMinDate()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMinDate()->sub(new \DateInterval('P1D')),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMinDate()->sub(new \DateInterval('P1D'))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMaxDate(),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMaxDate()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMaxDate()->add(new \DateInterval('P1D')),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMaxDate()->add(new \DateInterval('P1D'))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
        ];
    }

    /**
     * @dataProvider getInvalidDateInclusive
     */
    public function testIsValidReturnsFalseForInvalidDateForInclusiveRange($date, \DateTime $minDate, \DateTime $maxDate)
    {
        $format = DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY;

        $expectedMessages = [
            BetweenDatesValidator::INVALID_DATE_INCLUSIVE => str_replace(["%minDate%", "%maxDate%"], [$minDate->format($format), $maxDate->format($format)], BetweenDatesValidator::ERR_MSG_INVALID_DATE_INCLUSIVE)
        ];

        $this->assertIsValidReturnsFalse($date, $minDate, $maxDate, true, $expectedMessages);
    }

    public function getInvalidDateInclusive()
    {
        return [
            [
                $this->getMinDate()->sub(new \DateInterval('P1D')),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMinDate()->sub(new \DateInterval('P1D'))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMaxDate()->add(new \DateInterval('P1D')),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
            [
                $this->getMaxDate()->add(new \DateInterval('P1D'))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate(),
            ],
        ];
    }

    /**
     * @dataProvider getValidDate
     */
    public function testIsValidReturnsTrueForValidDateForNotInclusiveRange($date, \DateTime $minDate, \DateTime $maxDate)
    {
        $this->assertIsValidReturnsTrue($date, $minDate, $maxDate, false);
    }

    public function getValidDate()
    {
        return [
            [
                $this->getMinDate()->add(new \DateInterval("P1D")),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMinDate()->add(new \DateInterval("P10D"))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMaxDate()->sub(new \DateInterval("P1D")),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMaxDate()->sub(new \DateInterval("P10D"))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate()
            ]
        ];
    }

    /**
     * @dataProvider getValidDateInclusive
     */
    public function testIsValidReturnsTrueForValidDateForInclusiveRange($date, \DateTime $minDate, \DateTime $maxDate)
    {
        $this->assertIsValidReturnsTrue($date, $minDate, $maxDate, true);
    }

    public function getValidDateInclusive()
    {
        return [
            [
                $this->getMinDate(),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMinDate()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMinDate()->add(new \DateInterval("P1D")),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMinDate()->add(new \DateInterval("P10D"))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMaxDate(),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMaxDate()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMaxDate()->sub(new \DateInterval("P1D")),
                $this->getMinDate(),
                $this->getMaxDate()
            ],
            [
                $this->getMaxDate()->sub(new \DateInterval("P10D"))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->getMinDate(),
                $this->getMaxDate()
            ]
        ];
    }

    private function assertIsValidReturnsFalse($date, \DateTime $minDate, \DateTime $maxDate, $isInclusive, array $expectedMessages = [])
    {
        $this->assertIsValid(false,$date, $minDate, $maxDate, $isInclusive, $expectedMessages);
    }

    private function assertIsValidReturnsTrue($date, \DateTime $minDate, \DateTime $maxDate, $isInclusive, array $expectedMessages = [])
    {
        $this->assertIsValid(true, $date, $minDate, $maxDate, $isInclusive, $expectedMessages);
    }

    private function assertIsValid($expectedStatus, $date, \DateTime $minDate, \DateTime $maxDate, $isInclusive, array $expectedMessages = [])
    {
        $validator = $this->createValidator($minDate, $maxDate);
        $validator->setInclusive($isInclusive);

        $this->assertEquals($expectedStatus, $validator->isValid($date));
        $this->assertEquals($expectedMessages, $validator->getMessages());
    }

    private function createValidator(\DateTime $minDate, \DateTime $maxDate, $options = null)
    {
        return new BetweenDatesValidator($minDate, $maxDate, $options);
    }

    private function getMinDate()
    {
        return new \DateTime("2016-04-01");
    }

    private function getMaxDate()
    {
        return new \DateTime("2016-06-01");
    }
}
