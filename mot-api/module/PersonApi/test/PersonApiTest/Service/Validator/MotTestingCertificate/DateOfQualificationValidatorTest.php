<?php

namespace PersonApiTest\Service\Validator\MotTestingCertificate;

use PersonApi\Service\Validator\MotTestingCertificate\DateOfQualificationValidator;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Date\DateTimeApiFormat;

class DateOfQualificationValidatorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DateOfQualificationValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new DateOfQualificationValidator($this->createDateTimeHolder());
    }

    /**
     * @dataProvider getValidData
     */
    public function testIsValidReturnsTrueForValidData($date)
    {
        $isValid = $this->validator->isValid($date);

        $this->assertTrue($isValid);
        $this->assertEquals([], $this->validator->getMessages());
    }

    public function getValidData()
    {
        return [
            [
                (new \DateTime('2015-09-09'))->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
                $this->createDateTimeHolder()->getCurrentDate(),
            ],
        ];
    }

    /**
     * @dataProvider getInvalidData
     */
    public function testIsValidReturnsFalseForInvalidDate($date, array $expectedMessages)
    {
        $isValid = $this->validator->isValid($date);
        $message = $this->validator->getMessages();

        $this->assertFalse($isValid);

        $this->assertCount(count($expectedMessages), $message);
        $this->assertEquals($expectedMessages, $message);
    }

    public function getInvalidData()
    {
        $today = $this->createDateTimeHolder()->getCurrentDate();
        $today->add(new \DateInterval('P1D'));
        $dateTomorrow = $today->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY);

        return [
            [
                $dateTomorrow,
                [
                    DateOfQualificationValidator::MSG_IS_FUTURE_DATE => DateOfQualificationValidator::ERROR_IS_FUTURE_DATE,
                ],
            ],
            [
                '2012-12',
                [
                    DateOfQualificationValidator::MSG_INVALID_DATE_FORMAT => DateOfQualificationValidator::ERROR_INVALID_DATE_FORMAT,
                ],
            ],
            [
                '2012-13-13',
                [
                    DateOfQualificationValidator::MSG_INVALID_DATE_FORMAT => DateOfQualificationValidator::ERROR_INVALID_DATE_FORMAT,
                ],
            ],
            [
                '2012-01-66',
                [
                    DateOfQualificationValidator::MSG_INVALID_DATE_FORMAT => DateOfQualificationValidator::ERROR_INVALID_DATE_FORMAT,
                ],
            ],
            [
                '01-01-2016',
                [
                    DateOfQualificationValidator::MSG_INVALID_DATE_FORMAT => DateOfQualificationValidator::ERROR_INVALID_DATE_FORMAT,
                ],
            ],
            [
                '',
                [
                    DateOfQualificationValidator::MSG_IS_EMPTY => DateOfQualificationValidator::ERROR_IS_EMPTY,
                ],
            ],
            [
                null,
                [
                    DateOfQualificationValidator::MSG_IS_EMPTY => DateOfQualificationValidator::ERROR_IS_EMPTY,
                ],
            ],
        ];
    }

    private function createDateTimeHolder()
    {
        return new DateTimeHolder();
    }
}
