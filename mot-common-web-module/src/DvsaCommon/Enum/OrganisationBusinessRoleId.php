<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'organisation_business_role' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 * @codeCoverageIgnore
 */
class OrganisationBusinessRoleId
{
    const AUTHORISED_EXAMINER_DESIGNATED_MANAGER = 1;
    const AUTHORISED_EXAMINER_DELEGATE = 2;
    const AUTHORISED_EXAMINER_PRINCIPAL = 3;
    const DVSA_SCHEME_MANAGEMENT = 4;

    /**
     * @return array of values for the type OrganisationBusinessRoleId
     */
    public static function getAll()
    {
        return [
            self::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
            self::AUTHORISED_EXAMINER_DELEGATE,
            self::AUTHORISED_EXAMINER_PRINCIPAL,
            self::DVSA_SCHEME_MANAGEMENT,
        ];
    }

    /**
     * @param mixed $key a candidate OrganisationBusinessRoleId value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
