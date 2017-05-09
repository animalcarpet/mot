<?php

namespace Core\Catalog\BusinessRole;

use Application\Service\CatalogService;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\Lazy;

class BusinessRoleCatalog
{
    private $roles;

    public function __construct(CatalogService $catalog)
    {
        $this->roles = new Lazy(function () use ($catalog) {
            return $this->buildRolesFromCatalog($catalog);
        });
    }

    /**
     * @param $code
     *
     * @return BusinessRole
     */
    public function getByCode($code)
    {
        return $this->roles->value()[$code];
    }

    /**
     * @param CatalogService $catalog
     *
     * @return BusinessRole[]
     */
    private function buildRolesFromCatalog(CatalogService $catalog)
    {
        return ArrayUtils::mapWithKeys($catalog->getBusinessRoles(),
            function ($key, array $roleAsArray) {
                return $roleAsArray['code'];
            }, function ($key, array $roleAsArray) {
                return new BusinessRole(
                    $roleAsArray['code'],
                    $roleAsArray['name'],
                    $roleAsArray['role']
                );
            });
    }
}
