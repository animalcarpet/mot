<?php

namespace Site\ViewModel\MotTest;

use DvsaCommon\Dto\MotTesting\MotTestInProgressDto;

class MotTestInProgressViewModel
{
    /** @var MotTestInProgressDto */
    private $motTestInProgressDto;

    public function __construct(MotTestInProgressDto $motTestInProgressDto)
    {
        $this->motTestInProgressDto = $motTestInProgressDto;
    }

    public function getMotTestId()
    {
        return $this->motTestInProgressDto->getMotTestId();
    }

    public function getVehicleMake()
    {
        return $this->motTestInProgressDto->getVehicleMake();
    }

    public function getTesterName()
    {
        return $this->motTestInProgressDto->getTesterName();
    }

    /**
     * @return \DvsaCommon\Dto\Vehicle\ModelDto
     */
    public function getVehicleModel()
    {
        return $this->motTestInProgressDto->getVehicleModel();
    }

    public function getNumber()
    {
        return $this->motTestInProgressDto->getNumber();
    }

    public function getVrmOrItsAbsentReason()
    {
        $result = !is_null($this->motTestInProgressDto->getVehicleRegisteredNumber()) ?
            $this->motTestInProgressDto->getVehicleRegisteredNumber() :
            $this->motTestInProgressDto->getEmptyVrmReasonName();

        return $result;
    }
}
