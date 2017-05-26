<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace VehicleApi\Model;

class VehicleWeight
{
    /**
     * @var int
     */
    private $weight;

    /**
     * @var int
     */
    private $weightSource;

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     *
     * @return VehicleWeight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @return int
     */
    public function getWeightSource()
    {
        return $this->weightSource;
    }

    /**
     * @param int $weightSource
     *
     * @return VehicleWeight
     */
    public function setWeightSource($weightSource)
    {
        $this->weightSource = $weightSource;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasWeight()
    {
        return !empty($this->weight);
    }

    /**
     * @return bool
     */
    public function hasWeightSource()
    {
        return isset($this->weightSource);
    }
}
