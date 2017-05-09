<?php

namespace Dashboard\Service;

use Core\Catalog\BusinessRole\BusinessRole;
use Core\Catalog\EnumCatalog;
use DvsaCommon\ApiClient\Person\PersonTradeRoles\Dto\PersonTradeRoleDto;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Model\OrganisationBusinessRoleCode;

class PersonTradeRoleSorterService
{
    /**
     * Used for sorting.
     *
     * @var array
     */
    public static $roleWeights = [
        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER => 0,
        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE => 1,
        OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL => 2,

        SiteBusinessRoleCode::SITE_MANAGER => 0,
        SiteBusinessRoleCode::SITE_ADMIN => 1,
        SiteBusinessRoleCode::TESTER => 2,
    ];

    /**
     * @var EnumCatalog
     */
    protected $catalog;

    public function __construct(EnumCatalog $catalog)
    {
        $this->catalog = $catalog;
    }

    /**
     * @param PersonTradeRoleDto[] $tradeRoles
     *
     * @return array
     */
    public function sortTradeRoles($tradeRoles)
    {
        $groupedRoles = $this->groupRoles($tradeRoles);
        $sortedRoles = $this->sortRoles($groupedRoles);

        return $sortedRoles;
    }

    /**
     * @param PersonTradeRoleDto[] $tradeRoles
     *
     * @return array[array[PersonTradeRoleDto[]]
     */
    protected function groupRoles($tradeRoles)
    {
        $sortedRoles = [];
        foreach ($tradeRoles as $tradeRole) {
            if ($this->catalog->businessRole()->getByCode($tradeRole->getRoleCode())->getType() == BusinessRole::SITE_TYPE) {
                $sortedRoles[$tradeRole->getWorkplaceId()][BusinessRole::SITE_TYPE][] = $tradeRole;
            } else {
                $sortedRoles[$tradeRole->getAeId()][BusinessRole::ORGANISATION_TYPE][] = $tradeRole;
            }
        }

        return $sortedRoles;
    }

    /**
     * @param $groupedRoles
     *
     * @return array[array[PersonTradeRoleDto[]]
     */
    protected function sortRoles($groupedRoles)
    {
        foreach ($groupedRoles as &$roleTypes) {
            foreach ($roleTypes as &$roles) {
                usort($roles, [$this, 'usortRoles']);
            }
        }

        return $groupedRoles;
    }

    /**
     * usort implementation.
     *
     * @param PersonTradeRoleDto $roleA
     * @param PersonTradeRoleDto $roleB
     *
     * @return int
     */
    protected function usortRoles(PersonTradeRoleDto $roleA, PersonTradeRoleDto $roleB)
    {
        $roleAweight = 10;
        $roleBweight = 10;

        if (isset(static::$roleWeights[$roleA->getRoleCode()])) {
            $roleAweight = static::$roleWeights[$roleA->getRoleCode()];
        }
        if (isset(static::$roleWeights[$roleB->getRoleCode()])) {
            $roleBweight = static::$roleWeights[$roleB->getRoleCode()];
        }

        if ($roleAweight == $roleBweight) {
            return 0;
        }

        return ($roleAweight > $roleBweight) ? 1 : -1;
    }
}
