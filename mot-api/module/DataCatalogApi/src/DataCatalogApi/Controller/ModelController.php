<?php
namespace DataCatalogApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaEntities\Entity\Model;
use DataCatalogApi\Service\VehicleCatalogService;

/** */
class ModelController extends AbstractDvsaRestfulController
{

    public function getModelsAction()
    {
        $make = $this->params()->fromRoute("id");
        $models = $this->getVehicleCatalog()->getModelsByMakeId($make);
        $modelsData = array_map(
            function (Model $model) {
                return ['id' => $model->getId(), 'code' => $model->getCode(), 'name' => $model->getName()];
            },
            $models
        );
        return ApiResponse::jsonOk($modelsData);
    }

    public function getModelsByMakeIdAction()
    {
        $make = $this->params()->fromRoute("id");
        $models = $this->getVehicleCatalog()->getModelsByMakeId($make);
        $modelsData = array_map(
            function (Model $model) {
                return ['id' => $model->getId(), 'code' => $model->getCode(), 'name' => $model->getName()];
            },
            $models
        );
        return ApiResponse::jsonOk($modelsData);
    }

    /** @return VehicleCatalogService */
    private function getVehicleCatalog()
    {
        return $this->getServiceLocator()->get('VehicleCatalogService');
    }
}
