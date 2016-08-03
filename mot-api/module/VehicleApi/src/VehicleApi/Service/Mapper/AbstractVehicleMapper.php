<?php

namespace VehicleApi\Service\Mapper;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Dto\Vehicle\AbstractVehicleDto;
use DvsaCommon\Dto\Vehicle\MakeDto;
use DvsaCommon\Dto\Vehicle\ModelDetailDto;
use DvsaCommon\Dto\Vehicle\ModelDto;
use DvsaCommon\Dto\Vehicle\VehicleParamDto;
use DvsaCommon\Dto\VehicleClassification\VehicleClassDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaCommonApi\Service\Mapper\ColourMapper;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Entity\BodyType;

/**
 * Class AbstractVehicleMapper
 *
 * @package VehicleApi\Service\Mapper
 */
abstract class AbstractVehicleMapper extends AbstractApiMapper
{
    /**
     * @return AbstractVehicleDto[]
     */
    public function manyToDto($vehicles)
    {
        return parent::manyToDto($vehicles);
    }

    /**
     * @param Vehicle $vehicle
     *
     * @return AbstractVehicleDto
     */
    protected function mapCommonFieldsToDto(AbstractVehicleDto $dto, $vehicle)
    {
        $dto->setId($vehicle->getId());
        $dto->setRegistration($vehicle->getRegistration());
        $dto->setVin($vehicle->getVin());
        
        $dto->setManufactureDate(DateTimeApiFormat::date($vehicle->getManufactureDate()));
        $dto->setFirstRegistrationDate(DateTimeApiFormat::date($vehicle->getFirstRegistrationDate()));
        $dto->setFirstUsedDate(DateTimeApiFormat::date($vehicle->getFirstUsedDate()));

        $dto->setVehicleClass(new VehicleClassDto());
        $dto->setEngineNumber($vehicle->getEngineNumber());
        $dto->setCylinderCapacity($vehicle->getCylinderCapacity());
        $dto->setIsNewAtFirstReg($vehicle->isNewAtFirstReg());

        //  --  transmision type   --
        $dto->setTransmissionType(new VehicleParamDto());

        return $dto;
    }

    /**
     * @param BodyType $bodyType
     *
     * @return VehicleParamDto
     */
    public function getBodyTypeDto($bodyType)
    {
        $bodyTypeDto = new VehicleParamDto();

        if ($bodyType) {
            $bodyTypeDto
                ->setId($bodyType->getId())
                ->setCode($bodyType->getCode())
                ->setName($bodyType->getName());
        }
        return $bodyTypeDto;
    }
}
