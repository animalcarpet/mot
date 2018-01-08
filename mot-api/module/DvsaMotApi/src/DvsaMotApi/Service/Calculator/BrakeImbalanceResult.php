<?php
/**
 * Created by PhpStorm.
 * User: markpatt
 * Date: 06/12/2017
 * Time: 16:11
 */

namespace DvsaMotApi\Service\Calculator;


use Psr\Log\InvalidArgumentException;

class BrakeImbalanceResult
{

    const AXLE_1 = 'axle1';
    const AXLE_2 = 'axle2';
    const AXLE_3 = 'axle3';
    const PRIMARY_AXLE = 'primaryAxle';
    const SECONDARY_AXLE = 'secondaryAxle';
    const PRIMARY_AXLE_COMMENT = 'Primary Axle';
    const SECONDARY_AXLE_COMMENT = 'Secondary Axle';

    /** @var bool */
    private $isPassing;

    /** @var int */
    private $maxEffort;

    /** @var int */
    private $imbalanceValues;

    /** @var $imbalanceSeverity */
    private $imbalanceSeverities;

    /** @var  bool */
    private $imbalancePass;

    /** @var bool */
    private $parkingBrakeImbalancePass;

    public function __construct()
    {
        $this->maxEffort = array(
            "axle1" => 0,
            "axle2" => 0,
            "axle3" => 0,
            "primaryAxle" => 0,
            "secondaryAxle" => 0
        );
        $this->imbalanceValues = array(
            "axle1" => 0,
            "axle2" => 0,
            "axle3" => 0,
            "primaryAxle" => 0,
            "secondaryAxle" => 0
        );
        $this->imbalanceSeverities = array(
            "axle1" => CalculationFailureSeverity::NONE,
            "axle2" => CalculationFailureSeverity::NONE,
            "axle3" => CalculationFailureSeverity::NONE,
            "primaryAxle" => CalculationFailureSeverity::NONE,
            "secondaryAxle" => CalculationFailureSeverity::NONE
        );
        $this->isPassing = array(
            "axle1" => true,
            "axle2" => true,
            "axle3" => true,
            "primaryAxle" => true,
            "secondaryAxle" => true
        );
        $this->imbalancePass = true;
        $this->parkingBrakeImbalancePass = true;
    }


    /**
     * @param $effortOffside
     * @param $effortNearside
     */
    public function addAxleMaxEffort($axle, $effortOffside, $effortNearside)
    {
        $value = max(intval($effortOffside), intval($effortNearside));;
        $this->maxEffort[$axle] = $value;
    }

    /**
     * @param $imbalanceValue
     */
    public function addAxleImbalanceValue($axle, $imbalanceValue)
    {
        $this->imbalanceValues[$axle] = $imbalanceValue;
    }

    /**
     * @param $imbalanceSeverity
     */
    public function addAxleImbalanceSeverity($axle, $imbalanceSeverity)
    {
        $this->imbalanceSeverities[$axle] = $imbalanceSeverity;
    }

    /**
     * @return int
     */
    public function getAxleMaxEffort($axle) : int
    {
        return $this->maxEffort[$axle];
    }

    /**
     * @return int
     */
    public function getAxleImbalanceValue($axle) : int
    {
        return $this->imbalanceValues[$axle];
    }

    /**
     * @return string
     */
    public function getAxleImbalanceSeverity($axle) : string
    {
        return $this->imbalanceSeverities[$axle];
    }

    /**
     * @param $isPassing
     */
    public function setIsAxlePassing($axle, $isPassing)
    {
        $this->isPassing[$axle]  = $isPassing;
    }

    /**
     * @return bool
     */
    public function isAxlePassing($axle) : bool
    {
        return $this->isPassing[$axle];
    }

    /** @param $isPassing */
    public function setImbalanceOverallPass($isPassing)
    {
        if ($this->imbalancePass === false)
        {
            return;
        }
        $this->imbalancePass = $isPassing;
    }

    /**
     * @return bool
     */
    public function isImbalanceOverallPass() : bool
    {
        return $this->imbalancePass;
    }

    /**
     * @param $axle
     * @return string
     */
    public static function getAxleFromAxleNumber($axle)
    {
        if($axle == 1) {
            return self::AXLE_1;
        } else if($axle == 2) {
             return self::AXLE_2;
        } else if($axle == 3) {
             return self::AXLE_3;
        }

        throw new InvalidArgumentException('Invalid axle number provided => [' . $axle . ']');
    }

    /** @param $isPassing */
    public function setParkingBrakeImbalanceOverallPass($isPassing)
    {
        if ($this->parkingBrakeImbalancePass === false)
        {
            return;
        }
        $this->parkingBrakeImbalancePass = $isPassing;
    }

    /**
     * @return bool
     */
    public function isParkingBrakeImbalanceOverallPass() : bool
    {
        return $this->parkingBrakeImbalancePass;
    }
 }