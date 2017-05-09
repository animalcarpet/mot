<?php

namespace DataCatalogApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DataCatalogApi\Service\VehicleCatalogService;

/**
 * Class VehicleDictionaryController.
 */
class VehicleDictionaryController extends AbstractDvsaRestfulController
{
    /**
     * @return \Zend\View\Model\JsonModel
     */
    public function getList()
    {
        $searchType = $this->params()->fromQuery('searchType');
        $searchTerm = $this->params()->fromQuery('searchTerm');

        $result = [];
        if ($searchType === 'make') {
            $makes = $this->getVehicleCatalogService()->findMakeByName($searchTerm);
            $result = array_map(
                function (Make $make) {
                    return [
                        'id' => $make->getId(),
                        'code' => $make->getCode(),
                        'name' => $make->getName(),
                    ];
                },
                $makes
            );
        } elseif ($searchType === 'model') {
            $makeId = $this->params()->fromQuery('make');
            if ($makeId) {
                $makeModels = $this->getVehicleCatalogService()->findModelByNameAndMakeId($searchTerm, $makeId);
                $result = array_map(
                    function (Model $model) {
                        return [
                            'id' => $model->getId(),
                            'code' => $model->getCode(),
                            'name' => $model->getName(),
                        ];
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
        return $this->getServiceLocator()->get('VehicleCatalogService');
    }
}
