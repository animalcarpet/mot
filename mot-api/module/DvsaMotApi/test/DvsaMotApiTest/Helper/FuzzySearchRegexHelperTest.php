<?php

namespace DvsaMotApiTest\Helper;

use DvsaMotApi\Helper\FuzzySearchRegexHelper as SUT;

/**
 * Class FuzzySearchRegexHelperTest.
 */
class FuzzySearchRegexHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testUppercaseCharGroups()
    {
        //given
        $charGroup = [
            ['1', 'l', 'I'],
            ['6', 'G', 'b'],
        ];

        $expectedResult = [
            ['1', 'L', 'I'],
            ['6', 'G', 'B'],
        ];

        //when
        $result = SUT::uppercaseCharGroups($charGroup);

        //then
        $this->assertEquals($expectedResult, $result);
    }

    public function testCharGroupsToMapping()
    {
        //given
        $charGroups = [
            ['6', 'G'],
            ['9', 'G'],
        ];

        $expectedResult = [
            '6' => ['6', 'G'],
            'G' => ['6', 'G', '9'],
            '9' => ['9', 'G'],
        ];

        //when
        $result = SUT::charGroupsToMapping($charGroups);

        //then
        $this->assertEquals($expectedResult, $result);
    }

    public function testRegexForSimilarChars()
    {
        //given
        $string = 'test';
        $charMapping = [
            's' => ['s', '5'],
            't' => ['7', 't'],
        ];
        $expectedResult = '[7t]e[s5][7t]';

        //when
        $result = SUT::regexForSimilarChars($string, $charMapping);

        //then
        $this->assertEquals($expectedResult, $result);
    }
}
