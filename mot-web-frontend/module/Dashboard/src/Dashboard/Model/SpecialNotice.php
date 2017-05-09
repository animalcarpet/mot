<?php

namespace Dashboard\Model;

use DvsaCommon\Utility\ArrayUtils;

/**
 * Data for Special Notice component.
 */
class SpecialNotice
{
    /** @var $unreadCount int */
    private $unreadCount;

    /** @var $daysLeftToView int */
    private $daysLeftToView;

    /** @var $overdueCount int */
    private $overdueCount;

    public function __construct($data)
    {
        $this->setDaysLeftToView(ArrayUtils::get($data, 'daysLeftToView'));
        $this->setUnreadCount(ArrayUtils::get($data, 'unreadCount'));
        $this->setOverdueCount(ArrayUtils::get($data, 'overdueCount'));
    }

    public function toArray()
    {
        return [
            'unreadCount' => $this->getUnreadCount(),
            'daysLeftToView' => $this->getDaysLeftToView(),
            'overdueCount' => $this->getOverdueCount(),
        ];
    }

    /**
     * @param int $daysLeftToView
     *
     * @return SpecialNotice
     */
    public function setDaysLeftToView($daysLeftToView)
    {
        $this->daysLeftToView = $daysLeftToView;

        return $this;
    }

    /**
     * @return int
     */
    public function getDaysLeftToView()
    {
        return $this->daysLeftToView;
    }

    /**
     * @param int $overdueCount
     *
     * @return SpecialNotice
     */
    public function setOverdueCount($overdueCount)
    {
        $this->overdueCount = $overdueCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getOverdueCount()
    {
        return $this->overdueCount;
    }

    /**
     * @param int $unreadCount
     *
     * @return SpecialNotice
     */
    public function setUnreadCount($unreadCount)
    {
        $this->unreadCount = $unreadCount;

        return $this;
    }

    /**
     * @return int
     */
    public function getUnreadCount()
    {
        return $this->unreadCount;
    }
}
