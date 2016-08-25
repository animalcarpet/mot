<?php

namespace DvsaCommon\Model;

use DvsaCommon\Enum\RoleCode;

class TwoFaTesterApplicantRole
{
    public static function getTwoFaTesterApplicantRoles()
    {
        return [
            RoleCode::TESTER_ACTIVE,
            RoleCode::TESTER_APPLICANT_DEMO_TEST_REQUIRED,
        ];
    }

    public static function isTwoFaTesterApplicantRole($role)
    {
        return in_array($role, self::getTwoFaTesterApplicantRoles());
    }

    public static function containsTwoFaTesterApplicantRole($roles)
    {
        foreach ($roles as $role) {
            if(self::isTwoFaTesterApplicantRole($role)) {
                return true;
            }
        }

        return false;
    }
}