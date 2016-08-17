<?php

use Dvsa\Mot\Frontend\MotTestModule\ViewModel\DefectSentenceCaseConverter;

class DefectSentenceCaseConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testConversionOfLowerCaseString()
    {
        $testString = 'testing that a lower case string has its first word capitalised';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals(
            'Testing that a lower case string has its first word capitalised',
            $result
        );
    }

    public function testConversionOfStringWithAcronymAtStart()
    {
        $testString = 'TEST that a string with an acronym at the start is converted correctly';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals($testString, $result);
    }

    public function testAbsIsCapitalised()
    {
        $testString = 'Abs category';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals('ABS category', $result);
    }

    public function testSrsIsCapitalised()
    {
        $testString = 'Category for Srs';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals('Category for SRS', $result);
    }

    public function testStringOfAcronymns()
    {
        $testString = 'BBC DVSA DVLA';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals($testString, $result);
    }
}
