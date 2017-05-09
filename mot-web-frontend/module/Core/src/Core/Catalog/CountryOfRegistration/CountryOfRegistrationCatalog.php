<?php

namespace Core\Catalog\CountryOfRegistration;

use Application\Service\CatalogService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\Lazy;

class CountryOfRegistrationCatalog implements AutoWireableInterface
{
    private $countries;

    public function __construct(CatalogService $catalog)
    {
        $this->countries = new Lazy(function () use ($catalog) {
            return $this->buildCatalog($catalog);
        });
    }

    /**
     * @param $code
     *
     * @return CountryOfRegistration
     */
    public function getByCode($code)
    {
        return $this->countries->value()[$code];
    }

    /**
     * @return CountryOfRegistration[]
     */
    public function getAll()
    {
        return $this->countries->value();
    }

    /**
     * @param CatalogService $catalog
     *
     * @return CountryOfRegistration[]
     */
    private function buildCatalog(CatalogService $catalog)
    {
        return ArrayUtils::mapWithKeys($catalog->getCountriesOfRegistration(),
            function ($typeCode, $typeName) {
                return $typeCode;
            }, function ($code, $typeName) {
                return new CountryOfRegistration($code, $typeName);
            }
        );
    }
}
