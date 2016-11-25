<?php

namespace DvsaCommon\Utility;

use DvsaCommon\Enum\MotTestTypeCode;

/**
 * Class EnumUtils
 *
 * This class provides common business level functionality around data contained in the
 * auto-generated file "EnumType1Catalog.php"
 *
 * @package DvsaCommon\Utility
 */
class EnumUtils
{
    /**
     * Given the test type, it will determine firstly that the test type is valid, if not
     * we throw an exception to indicate that input data was bad. If the input data was
     * within the set of test types, we then determine if it was done by a normal tester
     * or not. Only the indicated types in the code are used to return "true".
     *
     * @param $testType
     *
     * @return bool if test is deemed to have been done by a tester
     * @throws \Exception if $testType is not a valid test type to begin with
     */
    public static function isTesterType($testType)
    {
        if (is_string($testType) && MotTestTypeCode::exists($testType)) {
            switch (strtoupper($testType)) {
                case MotTestTypeCode::NORMAL_TEST:
                case MotTestTypeCode::RE_TEST:
                case MotTestTypeCode::MYSTERY_SHOPPER:
                    return true;
            }
            return false;
        } else {
            throw new \Exception("Invalid Mot tester type");
        }
    }
}
