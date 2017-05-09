<?php

namespace EquipmentApiTest\Mapper;

use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\EquipmentMake;
use DvsaEntities\Entity\EquipmentModelStatus;
use DvsaEntities\Entity\EquipmentType;
use DvsaEntities\Entity\VehicleClass;
use EquipmentApi\Mapper\EquipmentModelMapper;
use DvsaEntities\Entity\EquipmentModel;
use DvsaCommon\Dto\Equipment\EquipmentModelDto;

class EquipmentModelMapperTest extends \PHPUnit_Framework_TestCase
{
    public function testMapperReturnsDto()
    {
        $mapper = new EquipmentModelMapper();
        $equipmentModel = XMock::of(EquipmentModel::class);
        $equipmentMake = XMock::of(EquipmentMake::class);
        $equipmentType = XMock::of(EquipmentType::class);
        $equipmentModelStatus = XMock::of(EquipmentModelStatus::class);
        $vehicleClass = XMock::of(VehicleClass::class);
        $equipmentModel->expects($this->once())->method('getMake')->willReturn($equipmentMake);
        $equipmentModel->expects($this->once())->method('getType')->willReturn($equipmentType);
        $equipmentModel->expects($this->once())->method('getStatus')->willReturn($equipmentModelStatus);
        $equipmentModel->expects($this->once())->method('getVehiclesClasses')->willReturn($vehicleClass);
        $dto = $mapper->toDto($equipmentModel);
        $this->assertInstanceOf(EquipmentModelDto::class, $dto);
    }
}
