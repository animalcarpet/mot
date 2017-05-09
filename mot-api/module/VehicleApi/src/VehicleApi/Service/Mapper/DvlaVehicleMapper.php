<?php

namespace VehicleApi\Service\Mapper;

use DataCatalogApi\Service\VehicleCatalogService;
use DvsaCommon\Dto\Vehicle\DvlaVehicleDto;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Dto\Vehicle\ModelDto;
use DvsaCommon\Dto\Vehicle\VehicleParamDto;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommonApi\Service\Mapper\ColourMapper;
use DvsaEntities\Entity\DvlaVehicle;

/**
 * Class DvlaVehicleMapper.
 */
class DvlaVehicleMapper extends AbstractVehicleMapper
{
    /**
     * @var VehicleCatalogService
     */
    private $vehicleCatalog;

    public function __construct($vehicleCatalog)
    {
        $this->vehicleCatalog = $vehicleCatalog;
    }

    /**
     * @param DvlaVehicle $vehicle
     *
     * @throws \Exception
     *
     * @return DvlaVehicleDto
     */
    public function toDto($vehicle)
    {
        TypeCheck::assertInstance($vehicle, DvlaVehicle::class);

        $dto = new DvlaVehicleDto();

        parent::mapCommonFieldsToDto($dto, $vehicle);

        // Full make name
        $dto->setMakeInFull($vehicle->getMakeInFull());

        //  ----  entity specific ----
        $dto->setDesignedGrossWeight($vehicle->getDesignedGrossWeight());
        $dto->setUnladenWeight($vehicle->getUnladenWeight());

        //  --  Colours --
        $dto->setColour($this->mapColourCodeToDto($vehicle->getPrimaryColour()));
        $dto->setColourSecondary($this->mapColourCodeToDto($vehicle->getSecondaryColour()));

        //  Make and Model details
        $makeCode = $vehicle->getMakeCode();
        $modelCode = $vehicle->getModelCode();
        $map = null;

        if (!$vehicle->getMakeInFull()) {
            $map = $this->vehicleCatalog->getMakeModelMapByDvlaCode($makeCode, $modelCode);
        }
        $makeEntity = $map ? $map->getMake() : null;
        $modelEntity = $map ? $map->getModel() : null;

        $makeDto = new MakeDto();
        if ($makeEntity) {
            $makeDto
                ->setId($makeEntity->getId())
                ->setCode($makeEntity->getCode())
                ->setName($makeEntity->getName());
        }

        $modelDto = new ModelDto();
        if ($modelEntity) {
            $modelDto
                ->setId($modelEntity->getId())
                ->setCode($modelEntity->getCode())
                ->setName($modelEntity->getName());
        }

        if ($modelDto->getName()) {
            $dto->setModelName($modelDto->getName());
        } else {
            if (!$vehicle->getMakeInFull()) {
                $dto->setModelName($this->vehicleCatalog->getModelNameByDvlaCode($makeCode, $modelCode));
            }
        }

        if ($makeDto->getName()) {
            $dto->setMakeName($makeDto->getName());
        } else {
            if ($vehicle->getMakeInFull()) {
                $dto->setMakeName($vehicle->getMakeInFull());
            } else {
                $dto->setMakeName($this->vehicleCatalog->getMakeNameByDvlaCode($makeCode));
            }
        }

        $fuelTypeDto = new VehicleParamDto();
        $fuelTypeCode = $vehicle->getFuelType();
        $fuelType = $this->vehicleCatalog->findFuelTypeByPropulsionCode($fuelTypeCode);
        if ($fuelType) {
            $fuelTypeDto
                ->setId($fuelType->getId())
                ->setCode($fuelType->getCode())
                ->setName($fuelType->getName());
        }

        $dto->setFuelType($fuelTypeDto);

        $bodyType = $this->vehicleCatalog->findBodyTypeByCode($vehicle->getBodyType());
        $dto->setBodyType($this->getBodyTypeDto($bodyType));

        return $dto;
    }

    private function mapColourCodeToDto($code)
    {
        return (new ColourMapper())->toDto($this->vehicleCatalog->findColourByCode($code));
    }
}
