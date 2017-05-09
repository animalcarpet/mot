<?php

namespace DvsaMotTest\Model;

/**
 * Class SlotDetails.
 */
class SlotDetails
{
    private $slots;

    private $slotsInUse;

    private $slotsWarning;

    public function __construct($slots, $slotsInUse, $slotsWarning)
    {
        $this->slots = $slots;
        $this->slotsInUse = $slotsInUse;
        $this->slotsWarning = $slotsWarning;
    }

    /**
     * @return mixed
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * @return mixed
     */
    public function getSlotsInUse()
    {
        return $this->slotsInUse;
    }

    /**
     * @return mixed
     */
    public function getSlotsWarning()
    {
        return $this->slotsWarning;
    }
}
