<?php

namespace Equipment\Controller;

use Core\ViewModel\Equipment\EquipmentModelViewModel;
use DvsaClient\MapperFactory;
use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Dto\Equipment\EquipmentModelDto;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class EquipmentController.
 */
class EquipmentController extends AbstractAuthActionController
{
    const ROUTE_MASTER = 'equipment';

    public function displayEquipmentListAction()
    {
        $equipmentModels = $this->getMapperFactory()->EquipmentModel->getAll();

        $equipmentModelStatusMap = $this->getCatalogService()->getEquipmentModelStatuses();

        $equipmentModelViewModel = ArrayUtils::map(
            $equipmentModels,
            function (EquipmentModelDto $equipmentModel) use ($equipmentModelStatusMap) {
                $status = $equipmentModelStatusMap[$equipmentModel->getStatus()];

                return new EquipmentModelViewModel($equipmentModel, $status);
            }
        );

        return ['equipmentModels' => $equipmentModelViewModel];
    }

    /**
     * @return MapperFactory
     */
    public function getMapperFactory()
    {
        return $this->getServiceLocator()->get(MapperFactory::class);
    }
}
