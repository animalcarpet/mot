<?php

namespace DvsaCommon\Dto\MotTesting;

use DvsaCommon\Dto\AbstractDataTransferObject;

/**
 * Represents a test that has not been finished.
 * Dto right now does not keep full set of information so feel free to expand.
 *
 * Class MotTestInProgressDto
 *
 * @package DvsaCommon\Dto\MotTesting
 */
class MotTestInProgressDto extends AbstractDataTransferObject
{

    private $motTestId;

    private $testerName;

    private $vehicleModel;

    private $number;

    private $vehicleRegisteredNumber;

    private $emptyVrmReasonName;

    private $vehicleMake;


    /**
     * @return int
     */
    public function getMotTestId()
    {
        return $this->motTestId;
    }

    /**
     * @param int $motTestId
     */
    public function setMotTestId($motTestId)
    {
        $this->motTestId = $motTestId;
    }

    public function setVehicleMake($vehicleMake)
    {
        $this->vehicleMake = $vehicleMake;
    }

    public function getVehicleMake()
    {
        return $this->vehicleMake;
    }

    /**
     * @return MotTestInProgressDto
     */
    public function setTesterName($testerName)
    {
        $this->testerName = $testerName;

        return $this;
    }

    public function getTesterName()
    {
        return $this->testerName;
    }

    public function setVehicleModel($vehicleModel)
    {
        $this->vehicleModel = $vehicleModel;

        return $this;
    }

    /**
     * @return \DvsaCommon\Dto\Vehicle\ModelDto
     */
    public function getVehicleModel()
    {
        return $this->vehicleModel;
    }

    /**
     * @return MotTestInProgressDto
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return MotTestInProgressDto
     */
    public function setVehicleRegisteredNumber($vehicleRegisteredNumber)
    {
        $this->vehicleRegisteredNumber = $vehicleRegisteredNumber;

        return $this;
    }

    public function getVehicleRegisteredNumber()
    {
        return $this->vehicleRegisteredNumber;
    }

    /**
     * @param string $reasonName
     * @return $this
     */
    public function setEmptyVrmReasonName($reasonName)
    {
        $this->emptyVrmReasonName = $reasonName;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmptyVrmReasonName()
    {
        return $this->emptyVrmReasonName;
    }
}
