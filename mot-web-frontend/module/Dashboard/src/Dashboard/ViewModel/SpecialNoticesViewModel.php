<?php

namespace Dashboard\ViewModel;

use Dashboard\Security\DashboardGuard;

class SpecialNoticesViewModel
{
    /** @var DashboardGuard $dashboardGuard */
    private $dashboardGuard;

    /** @var $unreadCount */
    private $unreadCount;

    /** @var $dayLeftToView */
    private $dayLeftToView;

    /** @var $overdueCount */
    private $overdueCount;

    /**
     * SpecialNoticeViewModel constructor.
     *
     * @param $unreadCount
     * @param $overdueCount
     * @param $daysLeftToView
     * @param DashboardGuard $dashboardGuard
     */
    public function __construct(
        $unreadCount,
        $overdueCount,
        $daysLeftToView,
        DashboardGuard $dashboardGuard)
    {
        $this->unreadCount = $unreadCount;
        $this->overdueCount = $overdueCount;
        $this->dayLeftToView = $daysLeftToView;
        $this->dashboardGuard = $dashboardGuard;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->dashboardGuard->canReceiveSpecialNotices();
    }

    /**
     * @return bool
     */
    public function isOverdue()
    {
        return $this->canAcknowledge() && $this->getNumberOfOverdueSpecialNotices() > 0;
    }

    /**
     * @return bool
     */
    public function isDaysLeftToViewVisible()
    {
        if ($this->getNumberOfUnreadNotices() > 0 &&
            $this->getNumberOfOverdueSpecialNotices() === 0 &&
            $this->canAcknowledge()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canAcknowledge()
    {
        return $this->dashboardGuard->canAcknowledgeSpecialNotices();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->dashboardGuard->canReadAllSpecialNotices() ? 'special-notices/all' : 'special-notices';
    }

    /**
     * @return int
     */
    public function getNumberOfUnreadNotices()
    {
        return $this->unreadCount;
    }

    /**
     * @return int
     */
    public function getNumberOfDaysLeftToView()
    {
        return $this->dayLeftToView;
    }

    /**
     * @return int
     */
    public function getNumberOfOverdueSpecialNotices()
    {
        return $this->overdueCount;
    }

    /**
     * @return DashboardGuard
     */
    public function getDashboardGuard()
    {
        return $this->dashboardGuard;
    }
}
