<?php

namespace VehicleApi\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Dto\Vehicle\ModelDetailDto;
use DvsaCommon\Dto\Vehicle\ModelDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Dto\Vehicle\VehicleParamDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommonApi\Service\Mapper\ColourMapper;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\BodyType;
use SebastianBergmann\Exporter\Exception;

/**
 * Class VehicleMapper
 */
class VehicleMapper extends AbstractVehicleMapper
{
    /**
     * @param Vehicle $vehicle
     *
     * @return VehicleDto
     * @throws \RuntimeException
     */
    public function toDto($vehicle)
    {
        TypeCheck::assertInstance($vehicle, Vehicle::class);

        $dto = new VehicleDto();

        parent::mapCommonFieldsToDto($dto, $vehicle);

        if (!is_null($vehicle->getEmptyVrmReason())) {
            $dto->setEmptyVrmReason($vehicle->getEmptyVrmReason()->getCode());
        }
        if (!is_null($vehicle->getEmptyVinReason())) {
            $dto->setEmptyVinReason($vehicle->getEmptyVinReason()->getCode());
        }
        //  ----  entity specific ----
        $dto->setYear($vehicle->getYear());
        $dto->setChassisNumber($vehicle->getChassisNumber());

        $dto->setWeight($vehicle->getWeight());

        $dto->setAmendedOn(DateTimeApiFormat::date($vehicle->getLastAmendedOn()));

        //  --  country of reg  --
        $dto->setCountryOfRegistration(
            (new CountryOfRegistrationMapper())->toDto($vehicle->getCountryOfRegistration())
        );

        $dto->setMakeName($vehicle->getMakeName());
        $dto->setModelName($vehicle->getModelName());

        //  --  transmision type   --
        $transmissionTypeDto = new VehicleParamDto();

        $param = $vehicle->getTransmissionType();
        if ($param) {
            $transmissionTypeDto
                ->setId($param->getId())
                ->setCode($param->getId())
                ->setName($param->getName());
        }

        $dto->setTransmissionType($transmissionTypeDto);

        //  --  Vehicle Class   --
        $classDto = new VehicleClassDto();

        $class = $vehicle->getVehicleClass();
        if ($class) {
            $classDto
                ->setId($class->getId())
                ->setName($class->getName())
                ->setCode($class->getCode())
                ->setGroup($class->getGroup());
        }

        $dto->setVehicleClass($classDto);

        //  --  Colours --
        $colourMapper = new ColourMapper();
        $dto->setColour($colourMapper->toDto($vehicle->getColour()));
        $dto->setColourSecondary($colourMapper->toDto($vehicle->getSecondaryColour()));

        $dto->setBodyType($this->getBodyTypeDto($vehicle->getBodyType()));

        $fuelTypeDto = new VehicleParamDto();
        $fuelType = $vehicle->getFuelType();
        if ($fuelType) {
            $fuelTypeDto
                ->setId($fuelType->getId())
                ->setCode($fuelType->getCode())
                ->setName($fuelType->getName());
        }

        $dto->setFuelType($fuelTypeDto);

        return $dto;
    }
}
