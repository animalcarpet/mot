<?php

namespace DvsaCommon\Domain;

use DvsaCommon\Enum\MotTestTypeCode;

/**
 * Mot test type model.
 *
 * @see \DvsaCommon\Enum\MotTestTypeCode
 * @Deprecated
 */
class MotTestType
{
    /**
     * @param string $testTypeCode
     *
     * @return bool
     */
    public static function isDemo($testTypeCode)
    {
        return in_array($testTypeCode, self::getDemoTypes(), false);
    }

    private static function getDemoTypes()
    {
        return [
            MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST,
            MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING,
        ];
    }

    /**
     * @param string $testTypeCode
     *
     * @return bool
     */
    public static function isSlotConsuming($testTypeCode)
    {
        return in_array($testTypeCode, self::getSlotConsumingTypes(), true);
    }

    public static function getSlotConsumingTypes()
    {
        return [
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::RE_TEST,
            MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
            MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
        ];
    }

    /**
     * @param string $testTypeCode
     *
     * @return bool
     *
     */
    public static function isReinspection($testTypeCode)
    {
        return in_array($testTypeCode, self::getReinspectionTypes(), true);
    }

    /**
     * @return MotTestType[]
     */
    private static function getReinspectionTypes()
    {
        return [
            MotTestTypeCode::TARGETED_REINSPECTION,
            MotTestTypeCode::MOT_COMPLIANCE_SURVEY,
            MotTestTypeCode::INVERTED_APPEAL,
            MotTestTypeCode::STATUTORY_APPEAL,
            MotTestTypeCode::OTHER,
        ];
    }

    /**
     * Normal tests that are conducted by testers at sites.
     *
     * @param $testTypeCode
     *
     * @return bool
     */
    public static function isStandard($testTypeCode)
    {
        return in_array($testTypeCode, self::getStandardTypes(), true);
    }

    /**
     * @return MotTestType[]
     */
    private static function getStandardTypes()
    {
        return [
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::RE_TEST,
            MotTestTypeCode::MYSTERY_SHOPPER,
        ];
    }

    /**
     * @param $testTypeCode
     *
     * @return bool
     */
    public static function isNonMotTypes($testTypeCode)
    {
        return in_array($testTypeCode, self::getNonMotTypes(), true);
    }

    /**
     * @return MotTestType[]
     */
    private static function getNonMotTypes()
    {
        return [
            MotTestTypeCode::NON_MOT_TEST,
        ];
    }

    /**
     * Is Type should generate VT32 report (Advisory)
     *
     * @param $testTypeCode
     *
     * @return bool
     */
    public static function isVeAdvisory($testTypeCode)
    {
        $veAdvisoryTypes = [
            MotTestTypeCode::TARGETED_REINSPECTION => 1,
            MotTestTypeCode::MOT_COMPLIANCE_SURVEY => 1,
            MotTestTypeCode::NON_MOT_TEST => 1,
        ];

        return !empty($veAdvisoryTypes[$testTypeCode]);
    }

    /**
     * @param $testTypeCode
     *
     * @return bool
     */
    public static function isRetest($testTypeCode)
    {
        return $testTypeCode === MotTestTypeCode::RE_TEST;
    }

    /**
     * Passing a test that has one of these types will move the vehicle expiry date forward.
     * If you do a test that has other type, even if the test passed, won't make the vehicle certified,
     * thus the expiry date will not be moved renewed.
     *
     * @return string[]
     */
    public static function getExpiryDateDefiningTypes()
    {
        return [
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
            MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
            MotTestTypeCode::INVERTED_APPEAL,
            MotTestTypeCode::STATUTORY_APPEAL,
            MotTestTypeCode::RE_TEST,
        ];
    }

    /**
     * @return array
     */
    public static function getMotTestHistoryTypes()
    {
        return [
            MotTestTypeCode::NORMAL_TEST,
            MotTestTypeCode::PARTIAL_RETEST_LEFT_VTS,
            MotTestTypeCode::PARTIAL_RETEST_REPAIRED_AT_VTS,
            MotTestTypeCode::TARGETED_REINSPECTION,
            MotTestTypeCode::MOT_COMPLIANCE_SURVEY,
            MotTestTypeCode::INVERTED_APPEAL,
            MotTestTypeCode::STATUTORY_APPEAL,
            MotTestTypeCode::OTHER,
            MotTestTypeCode::RE_TEST,
            MotTestTypeCode::NON_MOT_TEST
        ];
    }
}
