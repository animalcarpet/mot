<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Model;

/**
 * Class VehicleTestingStation.
 */
class VehicleTestingStation
{
    /**
     * @var int
     */
    protected $vtsId;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $address;

    /**
     * @var string
     */
    protected $siteNumber;

    /**
     * @var int
     */
    protected $slots = 0;

    /**
     * @var int
     */
    protected $slotsWarning = 0;

    /**
     * @var int
     */
    protected $slotsInUse = 0;

    /**
     * @var int
     */
    protected $authorisedExaminerId;

    public function __construct($data = [])
    {
        if (isset($data['id'])) {
            $this->vtsId = $data['id'];
        }

        if (isset($data['name'])) {
            $this->name = $data['name'];
        }

        if (isset($data['address'])) {
            $this->address = $data['address'];
        }

        if (isset($data['siteNumber'])) {
            $this->siteNumber = $data['siteNumber'];
        }

        if (isset($data['slots'])) {
            $this->slots = $data['slots'];
        }

        if (isset($data['slotsWarning'])) {
            $this->slotsWarning = $data['slotsWarning'];
        }

        if (isset($data['aeId'])) {
            $this->authorisedExaminerId = $data['aeId'];
        }

        if (isset($data['slotsInUse'])) {
            $this->slotsInUse = $data['slotsInUse'];
        }
    }

    /**
     * @param string $address
     *
     * @return VehicleTestingStation
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param int $authorisedExaminerId
     *
     * @return VehicleTestingStation
     */
    public function setAuthorisedExaminerId($authorisedExaminerId)
    {
        $this->authorisedExaminerId = $authorisedExaminerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getAuthorisedExaminerId()
    {
        return $this->authorisedExaminerId;
    }

    /**
     * @param string $name
     *
     * @return VehicleTestingStation
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $siteNumber
     *
     * @return VehicleTestingStation
     */
    public function setSiteNumber($siteNumber)
    {
        $this->siteNumber = $siteNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getSiteNumber()
    {
        return $this->siteNumber;
    }

    /**
     * @param int $slotsWarning
     *
     * @return VehicleTestingStation
     */
    public function setSlotsWarning($slotsWarning)
    {
        $this->slotsWarning = $slotsWarning;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlotsWarning()
    {
        return $this->slotsWarning;
    }

    /**
     * @param int $slots
     *
     * @return VehicleTestingStation
     */
    public function setSlots($slots)
    {
        $this->slots = $slots;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @param int $vtsId
     *
     * @return VehicleTestingStation
     */
    public function setVtsId($vtsId)
    {
        $this->vtsId = $vtsId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVtsId()
    {
        return $this->vtsId;
    }

    /**
     * @param int $slotsInUse
     *
     * @return VehicleTestingStation
     */
    public function setSlotsInUse($slotsInUse)
    {
        $this->slotsInUse = $slotsInUse;

        return $this;
    }

    /**
     * @return int
     */
    public function getSlotsInUse()
    {
        return $this->slotsInUse;
    }
}
