<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * BrakeTestResultServiceBrakeData.
 *
 * @ORM\Table(name="brake_test_result_service_brake_data")
 * @ORM\Entity
 */
class BrakeTestResultServiceBrakeData extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_nearside_axle1", type="integer", nullable=true)
     */
    private $effortNearsideAxle1;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_offside_axle1", type="integer", nullable=true)
     */
    private $effortOffsideAxle1;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_nearside_axle2", type="integer", nullable=true)
     */
    private $effortNearsideAxle2;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_offside_axle2", type="integer", nullable=true)
     */
    private $effortOffsideAxle2;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_nearside_axle3", type="integer", nullable=true)
     */
    private $effortNearsideAxle3;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_offside_axle3", type="integer", nullable=true)
     */
    private $effortOffsideAxle3;

    /**
     * @var int
     *
     * @ORM\Column(name="effort_single", type="integer", nullable=true)
     */
    private $effortSingle;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_nearside_axle1", type="boolean", nullable=true)
     */
    private $lockNearsideAxle1;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_offside_axle1", type="boolean", nullable=true)
     */
    private $lockOffsideAxle1;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_nearside_axle2", type="boolean", nullable=true)
     */
    private $lockNearsideAxle2;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_offside_axle2", type="boolean", nullable=true)
     */
    private $lockOffsideAxle2;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_nearside_axle3", type="boolean", nullable=true)
     */
    private $lockNearsideAxle3;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_offside_axle3", type="boolean", nullable=true)
     */
    private $lockOffsideAxle3;

    /**
     * @var bool
     *
     * @ORM\Column(name="lock_single", type="boolean", nullable=true)
     */
    private $lockSingle;

    /**
     * @var int
     *
     * @ORM\Column(name="imbalance_axle1", type="integer", nullable=true)
     */
    private $imbalanceAxle1;

    /**
     * @var int
     *
     * @ORM\Column(name="imbalance_axle2", type="integer", nullable=true)
     */
    private $imbalanceAxle2;

    /**
     * @var int
     *
     * @ORM\Column(name="imbalance_axle3", type="integer", nullable=true)
     */
    private $imbalanceAxle3;

    /**
     * @var bool
     *
     * @ORM\Column(name="imbalance_pass", type="boolean", nullable=true)
     */
    private $imbalancePass;

    /**
     * Stores imbalance pass/fail per axle (NON PERSISTENT).
     *
     * @var array
     */
    private $imbalancePassForAxle = [];

    /**
     * Set effortNearsideAxle1.
     *
     * @param int $effortNearsideAxle1
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortNearsideAxle1($effortNearsideAxle1)
    {
        $this->effortNearsideAxle1 = $effortNearsideAxle1;

        return $this;
    }

    /**
     * Get effortNearsideAxle1.
     *
     * @return int
     */
    public function getEffortNearsideAxle1()
    {
        return $this->effortNearsideAxle1;
    }

    /**
     * Set effortOffsideAxle1.
     *
     * @param int $effortOffsideAxle1
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortOffsideAxle1($effortOffsideAxle1)
    {
        $this->effortOffsideAxle1 = $effortOffsideAxle1;

        return $this;
    }

    /**
     * Get effortOffsideAxle1.
     *
     * @return int
     */
    public function getEffortOffsideAxle1()
    {
        return $this->effortOffsideAxle1;
    }

    /**
     * Set effortNearsideAxle2.
     *
     * @param int $effortNearsideAxle2
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortNearsideAxle2($effortNearsideAxle2)
    {
        $this->effortNearsideAxle2 = $effortNearsideAxle2;

        return $this;
    }

    /**
     * Get effortNearsideAxle2.
     *
     * @return int
     */
    public function getEffortNearsideAxle2()
    {
        return $this->effortNearsideAxle2;
    }

    /**
     * Set effortOffsideAxle2.
     *
     * @param int $effortOffsideAxle2
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortOffsideAxle2($effortOffsideAxle2)
    {
        $this->effortOffsideAxle2 = $effortOffsideAxle2;

        return $this;
    }

    /**
     * Get effortOffsideAxle2.
     *
     * @return int
     */
    public function getEffortOffsideAxle2()
    {
        return $this->effortOffsideAxle2;
    }

    /**
     * Set effortNearsideAxle3.
     *
     * @param int $effortNearsideAxle3
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortNearsideAxle3($effortNearsideAxle3)
    {
        $this->effortNearsideAxle3 = $effortNearsideAxle3;

        return $this;
    }

    /**
     * Get effortNearsideAxle3.
     *
     * @return int
     */
    public function getEffortNearsideAxle3()
    {
        return $this->effortNearsideAxle3;
    }

    /**
     * Set effortOffsideAxle3.
     *
     * @param int $effortOffsideAxle3
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortOffsideAxle3($effortOffsideAxle3)
    {
        $this->effortOffsideAxle3 = $effortOffsideAxle3;

        return $this;
    }

    /**
     * Get effortOffsideAxle3.
     *
     * @return int
     */
    public function getEffortOffsideAxle3()
    {
        return $this->effortOffsideAxle3;
    }

    /**
     * Set effortSingle.
     *
     * @param int $effortSingle
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setEffortSingle($effortSingle)
    {
        $this->effortSingle = $effortSingle;

        return $this;
    }

    /**
     * Get effortSingle.
     *
     * @return int
     */
    public function getEffortSingle()
    {
        return $this->effortSingle;
    }

    /**
     * Set lockNearsideAxle1.
     *
     * @param bool $lockNearsideAxle1
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockNearsideAxle1($lockNearsideAxle1)
    {
        $this->lockNearsideAxle1 = $lockNearsideAxle1;

        return $this;
    }

    /**
     * Get lockNearsideAxle1.
     *
     * @return bool
     */
    public function getLockNearsideAxle1()
    {
        return $this->lockNearsideAxle1;
    }

    /**
     * Set lockOffsideAxle1.
     *
     * @param bool $lockOffsideAxle1
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockOffsideAxle1($lockOffsideAxle1)
    {
        $this->lockOffsideAxle1 = $lockOffsideAxle1;

        return $this;
    }

    /**
     * Get lockOffsideAxle1.
     *
     * @return bool
     */
    public function getLockOffsideAxle1()
    {
        return $this->lockOffsideAxle1;
    }

    /**
     * Set lockNearsideAxle2.
     *
     * @param bool $lockNearsideAxle2
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockNearsideAxle2($lockNearsideAxle2)
    {
        $this->lockNearsideAxle2 = $lockNearsideAxle2;

        return $this;
    }

    /**
     * Get lockNearsideAxle2.
     *
     * @return bool
     */
    public function getLockNearsideAxle2()
    {
        return $this->lockNearsideAxle2;
    }

    /**
     * Set lockOffsideAxle2.
     *
     * @param bool $lockOffsideAxle2
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockOffsideAxle2($lockOffsideAxle2)
    {
        $this->lockOffsideAxle2 = $lockOffsideAxle2;

        return $this;
    }

    /**
     * Get lockOffsideAxle2.
     *
     * @return bool
     */
    public function getLockOffsideAxle2()
    {
        return $this->lockOffsideAxle2;
    }

    /**
     * Set lockNearsideAxle3.
     *
     * @param bool $lockNearsideAxle3
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockNearsideAxle3($lockNearsideAxle3)
    {
        $this->lockNearsideAxle3 = $lockNearsideAxle3;

        return $this;
    }

    /**
     * Get lockNearsideAxle3.
     *
     * @return bool
     */
    public function getLockNearsideAxle3()
    {
        return $this->lockNearsideAxle3;
    }

    /**
     * Set lockOffsideAxle3.
     *
     * @param bool $lockOffsideAxle3
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockOffsideAxle3($lockOffsideAxle3)
    {
        $this->lockOffsideAxle3 = $lockOffsideAxle3;

        return $this;
    }

    /**
     * Get lockOffsideAxle3.
     *
     * @return bool
     */
    public function getLockOffsideAxle3()
    {
        return $this->lockOffsideAxle3;
    }

    /**
     * Set lockSingle.
     *
     * @param bool $lockSingle
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setLockSingle($lockSingle)
    {
        $this->lockSingle = $lockSingle;

        return $this;
    }

    /**
     * Get lockSingle.
     *
     * @return bool
     */
    public function getLockSingle()
    {
        return $this->lockSingle;
    }

    /**
     * Set imbalanceAxle1.
     *
     * @param int $imbalanceAxle1
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setImbalanceAxle1($imbalanceAxle1)
    {
        $this->imbalanceAxle1 = $imbalanceAxle1;

        return $this;
    }

    /**
     * Get imbalanceAxle1.
     *
     * @return int
     */
    public function getImbalanceAxle1()
    {
        return $this->imbalanceAxle1;
    }

    /**
     * Set imbalanceAxle2.
     *
     * @param int $imbalanceAxle2
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setImbalanceAxle2($imbalanceAxle2)
    {
        $this->imbalanceAxle2 = $imbalanceAxle2;

        return $this;
    }

    /**
     * Get imbalanceAxle2.
     *
     * @return int
     */
    public function getImbalanceAxle2()
    {
        return $this->imbalanceAxle2;
    }

    /**
     * Set imbalanceAxle3.
     *
     * @param int $imbalanceAxle3
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setImbalanceAxle3($imbalanceAxle3)
    {
        $this->imbalanceAxle3 = $imbalanceAxle3;

        return $this;
    }

    /**
     * Get imbalanceAxle3.
     *
     * @return int
     */
    public function getImbalanceAxle3()
    {
        return $this->imbalanceAxle3;
    }

    /**
     * Set imbalancePass.
     *
     * @param bool $imbalancePass
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setImbalancePass($imbalancePass)
    {
        $this->imbalancePass = $imbalancePass;

        return $this;
    }

    /**
     * Get imbalancePass.
     *
     * @return bool
     */
    public function getImbalancePass()
    {
        return $this->imbalancePass;
    }

    /**
     * @param $axleNumber
     *
     * @return bool | null
     */
    public function getImbalancePassForAxle($axleNumber)
    {
        return isset($this->imbalancePassForAxle[$axleNumber]) ? $this->imbalancePassForAxle[$axleNumber] : null;
    }

    /**
     * @param $axleNumber
     * @param $pass
     *
     * @return BrakeTestResultServiceBrakeData
     */
    public function setImbalancePassForAxle($axleNumber, $pass)
    {
        $this->imbalancePassForAxle[$axleNumber] = $pass;

        return $this;
    }
}
