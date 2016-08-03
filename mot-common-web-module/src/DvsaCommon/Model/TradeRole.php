<?php

namespace DvsaCommon\Model;

use DvsaCommon\Enum\RoleCode;

class TradeRole
{
    public static function getTradeRoles()
    {
        return [
            RoleCode::TESTER,
            RoleCode::SITE_ADMIN,
            RoleCode::SITE_MANAGER,
            RoleCode::AUTHORISED_EXAMINER_DELEGATE,
            RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        ];
    }

    public static function containsTradeRole($roles)
    {
        foreach ($roles as $role) {
            if (self::isTradeRole($role)) {
                return true;
            }
        }

        return false;
    }

    public static function isTradeRole($role)
    {
        return in_array($role, self::getTradeRoles());
    }
}
