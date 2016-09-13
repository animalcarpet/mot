<?php

namespace DvsaCommon\Dto\MotTesting;

use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;

class MotTestOptionsDto
{
    /** @var string */
    private $motTestStartedDate;
    /** @var int */
    private $vehicleId;
    /** @var string */
    private $vehicleMake;
    /** @var string */
    private $vehicleModel;
    /** @var string */
    private $vehicleRegistrationNumber;
    /** @var MotTestTypeDto */
    private $motTestTypeDto;

    /**
     * @param array $data
     *
     * @return \DvsaCommon\Dto\MotTesting\MotTestOptionsDto
     */
    public static function fromArray($data)
    {
        TypeCheck::assertArray($data);

        $vehicle = ArrayUtils::get($data, 'vehicle');

        $dto = (new MotTestOptionsDto())->setMotTestStartedDate(ArrayUtils::get($data, 'startedDate'))
            ->setVehicleId(ArrayUtils::get($vehicle, 'id'))
            ->setVehicleMake(ArrayUtils::get($vehicle, 'make'))
            ->setVehicleModel(ArrayUtils::get($vehicle, 'model'))
            ->setVehicleRegistrationNumber(
                ArrayUtils::get($vehicle, 'vehicleRegistrationNumber')
            );

        $motTestType = ArrayUtils::get($data, 'motTestType');

        $dto->setMotTestTypeDto(
            (new MotTestTypeDto())->setId(ArrayUtils::get($motTestType, 'id'))
                                  ->setCode(ArrayUtils::get($motTestType, 'code'))
        );

        return $dto;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'startedDate' => $this->motTestStartedDate,
            'vehicle' => [
                'id' => $this->vehicleId,
                'make' => $this->vehicleMake,
                'model' => $this->vehicleModel,
                'vehicleRegistrationNumber' => $this->vehicleRegistrationNumber
            ],
            'motTestType' => [
                'id' => $this->getMotTestTypeDto()->getId(),
                'code' => $this->getMotTestTypeDto()->getCode()
            ]
        ];
    }

    /**
     * @return string
     */
    public function getVehicleMake()
    {
        return $this->vehicleMake;
    }

    /**
     * @return string
     */
    public function getVehicleModel()
    {
        return $this->vehicleModel;
    }

    /**
     * @return string
     */
    public function getVehicleRegistrationNumber()
    {
        return $this->vehicleRegistrationNumber;
    }

    /**
     * @return string
     */
    public function getMotTestStartedDate()
    {
        return $this->motTestStartedDate;
    }

    /**
     * @param $motTestStartedDate
     *
     * @return MotTestOptionsDto
     */
    public function setMotTestStartedDate($motTestStartedDate)
    {
        $this->motTestStartedDate = $motTestStartedDate;

        return $this;
    }

    /**
     * @param $vehicleMake
     *
     * @return MotTestOptionsDto
     */
    public function setVehicleMake($vehicleMake)
    {
        $this->vehicleMake = $vehicleMake;

        return $this;
    }

    /**
     * @param $vehicleModel
     *
     * @return MotTestOptionsDto
     */
    public function setVehicleModel($vehicleModel)
    {
        $this->vehicleModel = $vehicleModel;

        return $this;
    }

    /**
     * @param string $vehicleRegistrationNumber
     *
     * @return MotTestOptionsDto
     */
    public function setVehicleRegistrationNumber($vehicleRegistrationNumber)
    {
        $this->vehicleRegistrationNumber = $vehicleRegistrationNumber;

        return $this;
    }

    /**
     * @return MotTestTypeDto
     */
    public function getMotTestTypeDto()
    {
        return $this->motTestTypeDto;
    }

    /**
     * @param MotTestTypeDto $motTestTypeDto
     */
    public function setMotTestTypeDto($motTestTypeDto)
    {
        $this->motTestTypeDto = $motTestTypeDto;
        return $this;
    }

    /**
     * @return int
     */
    public function getVehicleId()
    {
        return $this->vehicleId;
    }

    /**
     * @param int $vehicleId
     * @return MotTestOptionsDto
     */
    public function setVehicleId($vehicleId)
    {
        $this->vehicleId = $vehicleId;
        return $this;
    }

}
