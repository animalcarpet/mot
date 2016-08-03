<?php

namespace DataCatalogApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DataCatalogApi\Service\VehicleCatalogService;

/**
 * Class VehicleDictionaryController
 */
class VehicleDictionaryController extends AbstractDvsaRestfulController
{
    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function getList()
    {
        $searchType = $this->params()->fromQuery("searchType");
        $searchTerm = $this->params()->fromQuery("searchTerm");

        $result = [];
        if ($searchType === 'make') {
            $makes = $this->getVehicleCatalogService()->findMakeByName($searchTerm);
            $result = array_map(
                function (Make $make) {
                    return [
                        'id' => $make->getCode(),
                        'code' => $make->getCode(),
                        'name' => $make->getName()
                    ];
                },
                $makes
            );
        } elseif ($searchType === 'model') {
            $makeCode = $this->params()->fromQuery("make");
            if ($makeCode) {
                $make = $this->getVehicleCatalogService()->findMakeByCode($makeCode);
                $makeModels = $this->getVehicleCatalogService()->findModelByNameAndCodeId($searchTerm, $make->getId());
                $result = array_map(
                    function (Model $model) {
                        return ['id' => $model->getId(), 'name' => $model->getName(), 'code' => $model->getCode()];
                    }, $makeModels
                );
            }
        }

        return ApiResponse::jsonOk($result);
    }

    /**
     * @return VehicleCatalogService
     */
    private function getVehicleCatalogService()
    {
        return $this->getServiceLocator()->get("VehicleCatalogService");
    }
}
