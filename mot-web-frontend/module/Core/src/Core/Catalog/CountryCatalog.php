<?php

namespace Core\Catalog;

use Application\Service\CatalogService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\Lazy;

class CountryCatalog implements AutoWireableInterface
{
    private $countries;

    public function __construct(CatalogService $catalog)
    {
        $this->countries = new Lazy(function () use ($catalog) {
            return $this->buildVtsCountriesCatalog($catalog);
        });
    }

    /**
     * @param $code
     *
     * @return Country
     */
    public function getByCode($code)
    {
        return $this->countries->value()[$code];
    }

    /**
     * @return Country[]
     */
    public function getAllVtsCountries()
    {
        return $this->countries->value();
    }

    /**
     * @param CatalogService $catalog
     *
     * @return Country[]
     */
    private function buildVtsCountriesCatalog(CatalogService $catalog)
    {
        return ArrayUtils::mapWithKeys($catalog->getCountries(),
            function ($key, $countryName) {
                return $key;
            }, function ($code, $countryName) {
                return new Country($code, $countryName);
            }
        );
    }
}
