<?php

namespace DvsaCommon\Enum;

/**
 * Enum class generated from the 'organisation_business_role' table
 *
 * DO NOT EDIT! -- THIS CLASS IS GENERATED BY mot-common-web-module/generate_enums.php
 */
class OrganisationBusinessRoleCode
{
    const AUTHORISED_EXAMINER_DESIGNATED_MANAGER = 'AUTHORISED-EXAMINER-DESIGNATED-MANAGER';
    const AUTHORISED_EXAMINER_DELEGATE = 'AUTHORISED-EXAMINER-DELEGATE';
    const AUTHORISED_EXAMINER_PRINCIPAL = 'AUTHORISED-EXAMINER-PRINCIPAL';
    const DVSA_SCHEME_MANAGEMENT = 'DVSA-SCHEME-MANAGEMENT';

    /**
     * @return array of values for the type OrganisationBusinessRoleCode
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
     * @param mixed $key a candidate OrganisationBusinessRoleCode value
     *
     * @return true if $key is in the list of known values for the type
     */
    public static function exists($key)
    {
        return in_array($key, self::getAll(), true);
    }
}
