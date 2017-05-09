<?php

namespace Core\ViewModel\Equipment;

use DvsaCommon\Dto\Equipment\EquipmentDto;

class EquipmentViewModel
{
    /** @var EquipmentModelViewModel */
    private $model;

    /** @var EquipmentDto */
    private $equipment;

    public function __construct(EquipmentDto $equipmentDto, $modelStatus)
    {
        $this->equipment = $equipmentDto;
        $this->model = new EquipmentModelViewModel($equipmentDto->getModel(), $modelStatus);
    }

    public function getDateAdded()
    {
        return  $this->equipment->getDateAdded();
    }

    public function getIdentifier()
    {
        return  $this->equipment->getId();
    }

    public function getSerialNumber()
    {
        return $this->equipment->getSerialNumber();
    }

    public function getModel()
    {
        return $this->model;
    }
}
