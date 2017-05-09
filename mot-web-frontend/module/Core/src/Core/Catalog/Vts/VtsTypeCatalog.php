<?php

namespace Core\Catalog\Vts;

use Application\Service\CatalogService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\Lazy;

class VtsTypeCatalog implements AutoWireableInterface
{
    private $vtsTypes;

    public function __construct(CatalogService $catalog)
    {
        $this->vtsTypes = new Lazy(function () use ($catalog) {
            return $this->buildVtsTypesCatalog($catalog);
        });
    }

    /**
     * @param $code
     *
     * @return VtsType
     */
    public function getByCode($code)
    {
        return $this->vtsTypes->value()[$code];
    }

    /**
     * @return VtsType[]
     */
    public function getAllVtsTypes()
    {
        return $this->vtsTypes->value();
    }

    /**
     * @param CatalogService $catalog
     *
     * @return VtsType[]
     */
    private function buildVtsTypesCatalog(CatalogService $catalog)
    {
        return ArrayUtils::mapWithKeys($catalog->getSiteTypes(),
            function ($siteTypeCode, $siteTypeName) {
                return $siteTypeCode;
            }, function ($code, $typeName) {
                return new VtsType($code, $typeName);
            }
        );
    }
}
