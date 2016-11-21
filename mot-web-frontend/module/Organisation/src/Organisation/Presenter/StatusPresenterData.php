<?php

namespace Organisation\Presenter;


use Core\ViewModel\Badge\Badge;

class StatusPresenterData
{
    private $status;

    /** @var Badge */
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