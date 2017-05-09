<?php

namespace DataCatalogApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaEntities\Entity\Make;
use DataCatalogApi\Service\VehicleCatalogService;

/**
 * Provides information about make.
 */
class MakeController extends AbstractDvsaRestfulController
{
    public function get($id)
    {
        $make = $this->getVehicleCatalog()->getMake($id);

        return ApiResponse::jsonOk(self::mapMakes([$make]));
    }

    public function getList()
    {
        $makes = $this->getVehicleCatalog()->getMakes();

        return ApiResponse::jsonOk(self::mapMakes($makes));
    }

    private static function mapMakes(array $makes)
    {
        return array_map(
            function (Make $make) {
                return [
                   'id' => $make->getId(),
                   'code' => $make->getCode(),
                   'name' => $make->getName(),
               ];
            },
            $makes
        );
    }

    /** @return VehicleCatalogService */
    private function getVehicleCatalog()
    {
        return $this->getServiceLocator()->get('VehicleCatalogService');
    }
}
