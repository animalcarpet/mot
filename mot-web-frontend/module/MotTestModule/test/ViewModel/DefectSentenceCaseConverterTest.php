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

    public function testStringOfAcronyms()
    {
        $testString = 'BBC DVSA DVLA';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals($testString, $result);
    }

    public function testWhitespace()
    {
        $testString = ' HMRC ABS RAC  ';

        $result = DefectSentenceCaseConverter::convert($testString);

        $this->assertEquals('HMRC ABS RAC', $result);
    }

    public function testWhitespaceWithAcronymExpansion()
    {
        $testString = ' HMRC ABS RAC  ';

        $result = DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($testString);

        $this->assertEquals('HMRC anti-lock brake system RAC', $result);
    }

    public function testSentenceCaseWithAcronymExpansion()
    {
        $testString = 'the ABS';

        $result = DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($testString);

        $this->assertEquals('The anti-lock brake system', $result);
    }

    public function testAcronymsExpandedOnlyOnce()
    {
        $testString = 'ABS ABS VIN VIN';

        $result = DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($testString);

        $this->assertEquals('Anti-lock brake system ABS vehicle identification number VIN', $result);
    }

    public function testUpperCaseAcronymsInApostrophies()
    {
        $testString = 'damage to zone \'A\'';

        $result = DefectSentenceCaseConverter::convertWithFirstOccurrenceOfAcronymsExpanded($testString);

        $this->assertEquals('Damage to zone \'A\'', $result);
    }
}
