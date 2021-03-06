<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'auth_for_testing_mot_status' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class AuthorisationForTestingMotStatusCode
{
    const UNKNOWN = 'UNKN';
    const INITIAL_TRAINING_NEEDED = 'ITRN';
    const DEMO_TEST_NEEDED = 'DMTN';
    const QUALIFIED = 'QLFD';
    const REFRESHER_NEEDED = 'RFSHN';
    const SUSPENDED = 'SPND';

    /**
     * @return array of values for the type AuthorisationForTestingMotStatusCode
     */
    public static function getAll()
    {
        return [
            self::UNKNOWN,
            self::INITIAL_TRAINING_NEEDED,
            self::DEMO_TEST_NEEDED,
            self::QUALIFIED,
            self::REFRESHER_NEEDED,
            self::SUSPENDED,
        ];
    }

    /**
     * @param mixed $key a candidate AuthorisationForTestingMotStatusCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
