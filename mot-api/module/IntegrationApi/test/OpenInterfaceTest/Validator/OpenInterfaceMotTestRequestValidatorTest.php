<?php

namespace MotTestResultTest\Service\Validator;

use IntegrationApi\OpenInterface\Validator\OpenInterfaceMotTestRequestValidator;
use PHPUnit_Framework_TestCase;

/**
 * Class OpenInterfaceMotTestRequestValidatorTest.
 */
class OpenInterfaceMotTestRequestValidatorTest extends PHPUnit_Framework_TestCase
{
    private $motTestRequestValidator;

    protected function setUp()
    {
        $this->motTestRequestValidator = new OpenInterfaceMotTestRequestValidator();
    }

    /**
     * @dataProvider getValidDates
     */
    public function testValidDate($date)
    {
        //given

        //when
        $this->motTestRequestValidator->validateDate($date);
        //then no exception thrown
    }

    /**
     * @dataProvider getInvalidDates
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testInvalidDate($date)
    {
        //given

        //when
        $this->motTestRequestValidator->validateDate($date);

        //then exception is thrown and below is not invoked
        $this->assertTrue(false, 'An exception expected to be thrown.');
    }

    public static function getValidDates()
    {
        return [
            ['20010101'],
        ];
    }

    public static function getInvalidDates()
    {
        return [
            [null],
            [''],
            ['123'],
            ['20011301'], //invalid month
            ['20010132'], //invalid day
            ['200101011'],
        ];
    }
}
