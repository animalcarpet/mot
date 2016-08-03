<?php

namespace Organisation\Presenter;


use Core\ViewModel\Sidebar\SidebarBadge;

class StatusPresenterData
{
    private $status;

    /** @var SidebarBadge */
    private $sidebarBadge;

    public function __construct($status, $sidebarBadge)
    {
        $this->sidebarBadge = $sidebarBadge;
        $this->status = $status;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getSidebarBadgeCssClass()
    {
        return $this->sidebarBadge->getCssClass();
    }
}