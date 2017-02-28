<?php

namespace Dashboard\ViewModel;

class SlotsViewModel
{
    /** @var $overallSlotCount */
    private $overallSlotCount;

    /** @var $overallSiteCount */
    private $overallSiteCount;

    /**
     * SlotsViewModel constructor.
     * @param $overallSlotCount
     * @param $overallSiteCount
     */
    public function __construct($overallSlotCount, $overallSiteCount)
    {
        $this->overallSlotCount = $overallSlotCount;
        $this->overallSiteCount = $overallSiteCount;
    }

    /**
     * @return bool
     */
    public function isOverallSiteCountVisible()
    {
        return $this->overallSiteCount > 1;
    }

    /**
     * @return int
     */
    public function getOverallSlotCount()
    {
        return $this->overallSlotCount;
    }

    /**
     * @return int
     */
    public function getOverallSiteCount()
    {
        return $this->overallSiteCount;
    }
}
