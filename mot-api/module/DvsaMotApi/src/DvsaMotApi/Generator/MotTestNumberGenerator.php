<?php

namespace DvsaMotApi\Generator;

/**
 * Generates a unique determined number ("MOT Test Number") by seed (that should be DB MOT Test Id).
 *
 * - Must not start with 0 (zero).
 * - Must be 12 digits long (11 digits for number + 1 digits for checksum).
 * - Must be deterministic (given the same seed (DB MOT Test Id), should generate the same MOT Test Number)
 * - Must not generate the same MOT Test Number for 2 different seeds
 *
 * Algorithm:
 *
 * The number is selected from a modulo multiplication group with a prime modulus. The prime is chosen to be just under
 * 90,000,000,000. The exponent is taken from the database identifier to guarantee uniqueness.
 *
 * 10,000,000,000 is added to the result to ensure it does not start with 0.
 *
 * If you change the modulus, the generator of the group can be found from Wolfram Alpha, e.g
 * http://www.wolframalpha.com/input/?i=primitive+roots+of+89999999933
 * Note the generator isn't necessarily a prime.
 *
 * The algorithm will run out after $modulus number of tests. This is the same order of magnitude as just using the
 * database ID.
 */
class MotTestNumberGenerator
{
    const EXCEPTION_MOT_TEST_NUMBER_BASE_TYPE = 'Mot Test Number Base type should be a string.';
    const EXCEPTION_INCORRECT_LENGTH = 'Mot Test Number Base should be 11 characters in length.';
    const EXCEPTION_MOT_TEST_NUMBER_BASE_STARTS_WITH_ZERO = "Mot Test Number Base must not start with '0'.";
    const EXCEPTION_MOT_TEST_ID_NEGATIVE = 'MOT Test ID should be greater than zero.';
    const EXCEPTION_MOT_TEST_ID_TYPE = 'MOT Test ID must an integer.';

    const MOT_TEST_NUMBER_BASE_LENGTH = 11;
    const MOT2_CHECKSUM_ADDITION = 4;

    /* @var integer $modulus */
    private static $modulus = 89999999933;
    /* @var integer $groupGenerator */
    private static $groupGenerator = 89999999901;
    /* @var integer $weightedMask */
    private static $weightedMask = 73173173173;
    /* @var integer $checksumDivisionInteger */
    private static $checksumDivisionInteger = 10;

    /**
     * Generates Mot Test Number by seed (DB MOT Test Id) - 12 digits string, must not start with "0".
     *
     * @param int $motTestId
     *
     * @return string
     */
    public static function generateMotTestNumber($motTestId)
    {
        $motNumberWithoutChecksumDigit = self::generateMotTestNumberBaseFromId($motTestId);

        return strval($motNumberWithoutChecksumDigit.self::generateChecksumDigit($motNumberWithoutChecksumDigit));
    }

    /**
     * Creates a seemingly random number based on a given integer.
     * The number generated is 11 digits long and does not begin with 0 (zero).
     *
     *  *** Public access only to unit test it. Shouldn't be used on its own. ***
     *
     * @param int $motTestId
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function generateMotTestNumberBaseFromId($motTestId)
    {
        if (false === is_int($motTestId)) {
            throw new \InvalidArgumentException(self::EXCEPTION_MOT_TEST_ID_TYPE);
        }

        if ($motTestId < 0) {
            throw new \InvalidArgumentException(self::EXCEPTION_MOT_TEST_ID_NEGATIVE);
        }

        $motTestNumberBase = bcpowmod(self::$groupGenerator, $motTestId, self::$modulus);

        // Making sure the number does not start with zero.
        $motTestNumberBase += 10000000000;

        return strval($motTestNumberBase);
    }

    /**
     * Creates a MOT2 checksum digit.
     * The number generated is created from using a weighted mask and a MOT2 addition number.
     *
     *  *** Public access only to unit test it. Shouldn't be used on its own. ***
     *
     * @param string $motTestNumberBase
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public static function generateChecksumDigit($motTestNumberBase)
    {
        if (false === is_string($motTestNumberBase) || false === is_numeric($motTestNumberBase)) {
            throw new \InvalidArgumentException(self::EXCEPTION_MOT_TEST_NUMBER_BASE_TYPE);
        }

        if (strpos($motTestNumberBase, '0') === 0) {
            throw new \InvalidArgumentException(self::EXCEPTION_MOT_TEST_NUMBER_BASE_STARTS_WITH_ZERO);
        }

        if (strlen($motTestNumberBase) !== self::MOT_TEST_NUMBER_BASE_LENGTH) {
            throw new \InvalidArgumentException(self::EXCEPTION_INCORRECT_LENGTH);
        }

        $motTestNumberBaseDigits = str_split($motTestNumberBase);
        $weightedMaskDigits = str_split(self::$weightedMask);

        $total = 0;
        for ($i = 0; $i < self::MOT_TEST_NUMBER_BASE_LENGTH; ++$i) {
            $total += (int) $motTestNumberBaseDigits[$i] * (int) $weightedMaskDigits[$i];
        }

        $mod = bcmod($total, self::$checksumDivisionInteger);

        return strval(((int) $mod + self::MOT2_CHECKSUM_ADDITION) % 10);
    }
}
