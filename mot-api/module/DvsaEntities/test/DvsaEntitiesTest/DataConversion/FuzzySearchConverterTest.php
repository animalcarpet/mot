<?php

use DvsaEntities\DataConversion\FuzzySearchConverter;

class FuzzySearchConverterTest extends \PHPUnit_Framework_TestCase
{
    private $fuzzySearchConverter;

    public function setUp()
    {
        $this->fuzzySearchConverter = new FuzzySearchConverter();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testConvertingChars($input, $expectedResult)
    {
        $result = $this->fuzzySearchConverter->convert($input);

        $this->assertSame($expectedResult, $result);
    }

    public function dataProvider()
    {
        return [
            ['-', ''],
            ['OIZEASGTLB-/. ', '0123456778'],
            [null, null],
        ];
    }
}
