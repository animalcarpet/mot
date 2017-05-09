<?php

namespace DvsaMotApiTest\Generator;

use DvsaMotApi\Generator\MotTestNumberGenerator;

/**
 * unit tests for DvsaMotApiTest\Generator.
 */
class MotTestNumberGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Can generate a MOT Test Number.
     *
     * Check the following:
     * - the number generated is eleven characters long.
     * - the number does not start with a zero.
     * - the numbers generated are unique.
     */
    public function testGenerateMotTestNumber()
    {
        $motTestNumber = MotTestNumberGenerator::generateMotTestNumberBaseFromId(2);
        $this->assertCorrectCharacterLength($motTestNumber);
        $this->assertDoesNotStartWithZero($motTestNumber);

        $motTestNumberTwo = MotTestNumberGenerator::generateMotTestNumberBaseFromId(200000000);
        $this->assertCorrectCharacterLength($motTestNumberTwo);
        $this->assertDoesNotStartWithZero($motTestNumberTwo);

        $motTestNumberThree = MotTestNumberGenerator::generateMotTestNumberBaseFromId(3888999777);
        $this->assertCorrectCharacterLength($motTestNumberThree);
        $this->assertDoesNotStartWithZero($motTestNumberThree);

        $motTestNumberFour = MotTestNumberGenerator::generateMotTestNumberBaseFromId(89999999933);
        $this->assertCorrectCharacterLength($motTestNumberFour);
        $this->assertDoesNotStartWithZero($motTestNumberFour);

        $testNumberFive = MotTestNumberGenerator::generateMotTestNumberBaseFromId(89999999929);
        $this->assertCorrectCharacterLength($testNumberFive);
        $this->assertDoesNotStartWithZero($testNumberFive);

        $motTestNumbersGenerated = [
            $motTestNumber,
            $motTestNumberTwo,
            $motTestNumberThree,
            $motTestNumberFour,
            $testNumberFive,
        ];

        $this->assertUniqueMotNumbers($motTestNumbersGenerated);
    }

    /**
     * Check the max numbers before duplicates.
     *
     * The total amount of unique number is the largest prime used in the random number generator class.
     */
    public function testMaxNumberBeforeDuplicate()
    {
        $testNumberBase = MotTestNumberGenerator::generateMotTestNumberBaseFromId(89999999933);
        $this->assertCorrectCharacterLength($testNumberBase);
        $this->assertDoesNotStartWithZero($testNumberBase);

        $testNumberBaseTwo = MotTestNumberGenerator::generateMotTestNumberBaseFromId(1);
        $this->assertCorrectCharacterLength($testNumberBaseTwo);
        $this->assertDoesNotStartWithZero($testNumberBaseTwo);

        $motTestNumbersGenerated = [
            $testNumberBase,
            $testNumberBaseTwo,
        ];

        $this->assertEquals(1, count(array_unique($motTestNumbersGenerated)));
    }

    /**
     * Can generate the same MOT Test Number.
     *
     * As the number is pseudorandom, check that the same seed (DB MOT Test Id) produces the same MOT Test Number
     */
    public function testGenerateSameMotTestNumberFromSeed()
    {
        $motTestId = 100;

        $motTestNumberBase = MotTestNumberGenerator::generateMotTestNumberBaseFromId($motTestId);
        $motTestNumberBaseTwo = MotTestNumberGenerator::generateMotTestNumberBaseFromId($motTestId);
        $this->assertSame($motTestNumberBase, $motTestNumberBaseTwo);
    }

    /**
     * Can generate a MOT Test Number checksum digit.
     */
    public function testGenerateMotTestNumberChecksumDigit()
    {
        $expectedChecksumDigit = 4;
        $motTestNumberBase = '12345678901';

        $calculatedChecksumDigit = MotTestNumberGenerator::generateChecksumDigit($motTestNumberBase);
        $this->assertEquals($expectedChecksumDigit, $calculatedChecksumDigit);
    }

    /**
     * Can correctly strip leading digit from two digit checksum.
     *
     * As we are adding 4 to the modulus of 10 for mot2, there is a chance
     * that 6+ is return so the checksum will work out 10+. In this case only
     * the second digit should be used.
     */
    public function testStripLeadingDigitFromTwoDigitChecksum()
    {
        $checksumDigit = 9;
        $motTestNumberBase = '43651139692';

        // This will generate 13 as a checksum without striping the leading digit.
        $motChecksum = MotTestNumberGenerator::generateChecksumDigit($motTestNumberBase);
        $this->assertCorrectChecksumCharacterLength($motChecksum);
        $this->assertEquals($checksumDigit, $motChecksum);
    }

    /**
     * Can generate a MOT Test Number and the checksum digit.
     */
    public function testGenerateMotTestNumberAndChecksumDigit()
    {
        $motTestId = 100;
        $checksumDigit = '9';
        $motTestNumberBase = '43651139692';

        $motTestNumber = MotTestNumberGenerator::generateMotTestNumber($motTestId);
        $this->assertEquals($motTestNumberBase.$checksumDigit, $motTestNumber);
    }

    /**
     * Cannot pass incorrect mot generate id.
     *
     * @dataProvider incorrectMotGenerateIdProvider
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrectGenerateIdThrowsException($id)
    {
        MotTestNumberGenerator::generateMotTestNumberBaseFromId($id);
    }

    /**
     * Cannot give MOT Number with incorrect character length.
     *
     * @dataProvider incorrectMotLengthCharactersProvider
     * @expectedException \InvalidArgumentException
     */
    public function testIncorrectMotLengthThrowsException($motTestNumber)
    {
        MotTestNumberGenerator::generateChecksumDigit($motTestNumber);
    }

    /**
     * TEST HELPERS.
     */
    private function assertCorrectCharacterLength($data)
    {
        $requiredTestNumberLength = 11;
        $this->assertEquals($requiredTestNumberLength, strlen($data));
    }

    private function assertCorrectChecksumCharacterLength($data)
    {
        $requiredNumberLength = 1;
        $this->assertEquals($requiredNumberLength, strlen($data));
    }

    private function assertDoesNotStartWithZero($data)
    {
        $this->assertTrue(substr(strval($data), 0, 1) !== '0');
    }

    private function assertUniqueMotNumbers(array $testNumbers)
    {
        $uniqueTestNumbers = array_unique($testNumbers);
        $this->assertEquals(count($testNumbers), count($uniqueTestNumbers));
    }

    /**
     * DATA PROVIDERS.
     */
    public function incorrectMotLengthCharactersProvider()
    {
        return [
            [0],
            [1234],
            [1234567891011],
            ['0'],
            ['1234'],
            ['1234567891011'],
            ['01234567891'],
            [null],
            [true],
            [''],
            [new \stdClass()],
        ];
    }

    public function incorrectMotGenerateIdProvider()
    {
        return [
            [-10],
            [null],
            [true],
            [new \stdClass()],
        ];
    }
}
