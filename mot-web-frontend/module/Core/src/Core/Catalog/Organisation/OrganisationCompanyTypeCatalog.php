<?php

namespace Core\Catalog\Organisation;

use Application\Service\CatalogService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\Lazy;

class OrganisationCompanyTypeCatalog implements AutoWireableInterface
{
    private $organisationCompanyType;

    public function __construct(CatalogService $catalog)
    {
        $this->organisationCompanyType = new Lazy(function () use ($catalog) {
            return $this->buildOrganisationCompanyTypeCatalog($catalog);
        });
    }

    /**
     * @param $code
     *
     * @return OrganisationCompanyType
     */
    public function getByCode($code)
    {
        return $this->organisationCompanyType->value()[$code];
    }

    /**
     * @param $name
     *
     * @return OrganisationCompanyType|null
     */
    public function getByName($name)
    {
        foreach ($this->organisationCompanyType->value() as $companyType) {
            if ($companyType->getName() == $name) {
                return $companyType;
            }
        }
    }

    /**
     * @return OrganisationCompanyType[]
     */
    public function getAllCompanyTypes()
    {
        return $this->organisationCompanyType->value();
    }

    /**
     * @param CatalogService $catalog
     *
     * @return OrganisationCompanyType[]
     */
    private function buildOrganisationCompanyTypeCatalog(CatalogService $catalog)
    {
        return ArrayUtils::mapWithKeys($catalog->getOrganisationCompanyTypes(),
            function ($typeCode, $typeName) {
                return $typeCode;
            }, function ($code, $typeName) {
                return new OrganisationCompanyType($code, $typeName);
            }
        );
    }
}
