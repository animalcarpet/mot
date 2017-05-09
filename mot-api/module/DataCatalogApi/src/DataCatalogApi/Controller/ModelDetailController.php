<?php

namespace DataCatalogApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaEntities\Entity\ModelDetail;
use DataCatalogApi\Service\VehicleCatalogService;

/**
 * Class ModelDetailController.
 */
class ModelDetailController extends AbstractDvsaRestfulController
{
    public function getModelDetailsAction()
    {
        $make = $this->params()->fromRoute('id');
        $model = $this->params()->fromRoute('model');
        $modelDetails = $this->getVehicleCatalog()->getModelDetailsByModel($make, $model);

        $modelDetailsData = array_map(
            function (ModelDetail $md) {
                return ['id' => $md->getId(), 'name' => $md->getName()];
            },
            $modelDetails
        );

        return ApiResponse::jsonOk($modelDetailsData);
    }

    /** @return VehicleCatalogService */
    private function getVehicleCatalog()
    {
        return $this->getServiceLocator()->get('VehicleCatalogService');
    }
}
