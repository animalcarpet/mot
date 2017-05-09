<?php

namespace UserApi\Dashboard\Dto;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Utility\ArrayUtils;

/**
 * data about special notice.
 */
class SpecialNotice
{
    /** @var $unreadCount int */
    private $unreadCount;
    /** @var $overdueCount int */
    private $overdueCount;
    /** @var $daysLeftToView int */
    private $daysLeftToView;

    public function __construct($specialNotice)
    {
        $this->setUnreadCount(ArrayUtils::get($specialNotice, 'unreadCount'));
        $this->setOverdueCount(ArrayUtils::get($specialNotice, 'overdueCount'));
        $this->setDaysLeftToView(0);

        if ($this->getUnreadCount() > 0) {
            $ackDeadlineStr = ArrayUtils::get($specialNotice, 'acknowledgementDeadline');

            if (null !== $ackDeadlineStr) {
                $this->setDaysLeftToView(
                    DateUtils::getDaysDifference(
                        DateUtils::today(),
                        DateUtils::toDate($ackDeadlineStr)
                    )
                );
            }
        }
    }

    /**
     * @return array
     */
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
        $this->daysLeftToView = intval($daysLeftToView);

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
        $this->overdueCount = intval($overdueCount);

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
        $this->unreadCount = intval($unreadCount);

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
