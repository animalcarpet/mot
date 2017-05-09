<?php

namespace DvsaClient\Entity;

/**
 * Class VehicleTestingStation.
 */
class VehicleTestingStation extends Site
{
    private $id;
    private $tradingAs;
    private $vehicleTestingStationRef;
    private $testClasses;
    private $testLaneType;
    private $status;

    /**
     * @param string $id
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string[] $testClasses
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setTestClasses($testClasses)
    {
        $this->testClasses = $testClasses;

        return $this;
    }

    /**
     * @return string[]
     * @codeCoverageIgnore
     */
    public function getTestClasses()
    {
        return $this->testClasses;
    }

    /**
     * @param string $testLaneType
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setTestLaneType($testLaneType)
    {
        $this->testLaneType = $testLaneType;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getTestLaneType()
    {
        return $this->testLaneType;
    }

    /**
     * @param string $tradingAs
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setTradingAs($tradingAs)
    {
        $this->tradingAs = $tradingAs;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getTradingAs()
    {
        return $this->tradingAs;
    }

    /**
     * @param string $vehicleTestingStationRef
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setVehicleTestingStationRef($vehicleTestingStationRef)
    {
        $this->vehicleTestingStationRef = $vehicleTestingStationRef;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getVehicleTestingStationRef()
    {
        return $this->vehicleTestingStationRef;
    }

    /**
     * @param string $status
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getStatus()
    {
        return $this->status;
    }
}
