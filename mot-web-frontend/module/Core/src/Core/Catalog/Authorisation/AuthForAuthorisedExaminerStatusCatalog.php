<?php

namespace Core\Catalog\Authorisation;

use Application\Service\CatalogService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\Lazy;

class AuthForAuthorisedExaminerStatusCatalog implements AutoWireableInterface
{
    private $authForAuthorisedExaminerStatus;

    public function __construct(CatalogService $catalog)
    {
        $this->authForAuthorisedExaminerStatus = new Lazy(function () use ($catalog) {
            return $this->buildAuthForAuthorisedExaminerStatusCatalog($catalog);
        });
    }

    /**
     * @param $code
     *
     * @return AuthForAuthorisedExaminerStatus
     */
    public function getByCode($code)
    {
        return $this->authForAuthorisedExaminerStatus->value()[$code];
    }

    /**
     * @param $name
     *
     * @return AuthForAuthorisedExaminerStatus|null
     */
    public function getByName($name)
    {
        foreach ($this->authForAuthorisedExaminerStatus->value() as $authStatus) {
            if ($authStatus->getName() == $name) {
                return $authStatus;
            }
        }
    }

    /**
     * @return AuthForAuthorisedExaminerStatus[]
     */
    public function getAllSiteStatuses()
    {
        return $this->authForAuthorisedExaminerStatus->value();
    }

    /**
     * @param CatalogService $catalog
     *
     * @return AuthForAuthorisedExaminerStatus[]
     */
    private function buildAuthForAuthorisedExaminerStatusCatalog(CatalogService $catalog)
    {
        return ArrayUtils::mapWithKeys($catalog->getAuthForAuthorisedExaminerStatuses(),
            function ($typeCode, $typeName) {
                return $typeCode;
            }, function ($code, $typeName) {
                return new AuthForAuthorisedExaminerStatus($code, $typeName);
            }
        );
    }
}
