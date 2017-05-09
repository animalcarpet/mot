<?php

namespace Dashboard\ViewModel;

class AuthorisedExaminerManagementViewModel
{
    /** @var bool $canCreateAuthorisedExaminer */
    private $canCreateAuthorisedExaminer;

    /** @var bool $canCreateVehicleTestingStation */
    private $canCreateVehicleTestingStation;

    /**
     * AuthorisedExaminerManagementViewModel constructor.
     *
     * @param $canCreateAuthorisedExaminer
     * @param $canCreateVehicleTestingStation
     */
    public function __construct($canCreateAuthorisedExaminer, $canCreateVehicleTestingStation)
    {
        $this->canCreateAuthorisedExaminer = $canCreateAuthorisedExaminer;
        $this->canCreateVehicleTestingStation = $canCreateVehicleTestingStation;
    }

    /**
     * @return bool
     */
    public function getCanCreateAuthorisedExaminer()
    {
        return $this->canCreateAuthorisedExaminer;
    }

    /**
     * @return bool
     */
    public function getCanCreateVehicleTestingStation()
    {
        return $this->canCreateVehicleTestingStation;
    }
}
