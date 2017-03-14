<?php

namespace Dashboard\ViewModel;

class SlotsViewModel
{
    /** @var int $overallSlotCount */
    private $overallSlotCount;

    /** @var int $overallSiteCount */
    private $overallSiteCount;

    /** @var bool $canViewSlotBalance */
    private $canViewSlotBalance;

    /**
     * SlotsViewModel constructor.
     *
     * @param $canViewSlotBalance
     * @param $overallSlotCount
     * @param $overallSiteCount
     */
    public function __construct($canViewSlotBalance, $overallSlotCount, $overallSiteCount)
    {
        $this->canViewSlotBalance = $canViewSlotBalance;
        $this->overallSlotCount = $overallSlotCount;
        $this->overallSiteCount = $overallSiteCount;
    }

    /**
     * @return bool
     */
    public function canViewSlotBalance()
    {
        return $this->canViewSlotBalance;
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
