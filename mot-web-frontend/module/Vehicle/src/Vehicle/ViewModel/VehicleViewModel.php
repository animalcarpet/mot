<?php


namespace Vehicle\ViewModel;


use Core\ViewModel\Gds\Table\GdsTable;
use Core\ViewModel\Header\HeaderTertiaryList;
use Vehicle\ViewModel\Sidebar\VehicleSidebar;

class VehicleViewModel
{
    protected $vehicleSpecificationGdsTable;
    protected $vehicleRegistrationGdsTable;
    protected $backUrl;
    protected $pageTitle;
    protected $pageSecondaryTitle;
    /** @var HeaderTertiaryList */
    protected $pageTertiaryTitle;
    protected $backLinkText;
    /** @var VehicleSidebar */
    protected $sidebar;
    /** @var array */
    protected $breadcrumbs;

    /**
     * @return GdsTable
     */
    public function getVehicleSpecificationGdsTable()
    {
        return $this->vehicleSpecificationGdsTable;
    }

    /**
     * @param GdsTable $vehicleSpecificationGdsTable
     * @return VehicleViewModel
     */
    public function setVehicleSpecificationGdsTable($vehicleSpecificationGdsTable)
    {
        $this->vehicleSpecificationGdsTable = $vehicleSpecificationGdsTable;
        return $this;
    }

    /**
     * @return GdsTable
     */
    public function getVehicleRegistrationGdsTable()
    {
        return $this->vehicleRegistrationGdsTable;
    }

    /**
     * @param GdsTable $vehicleRegistrationGdsTable
     * @return VehicleViewModel
     */
    public function setVehicleRegistrationGdsTable($vehicleRegistrationGdsTable)
    {
        $this->vehicleRegistrationGdsTable = $vehicleRegistrationGdsTable;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->backUrl;
    }

    /**
     * @param string $backUrl
     * @return VehicleViewModel
     */
    public function setBackUrl($backUrl)
    {
        $this->backUrl = $backUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     * @return VehicleViewModel
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
        return $this;
    }

    /**
     * @return string
     */
    public function getBackLinkText()
    {
        return $this->backLinkText;
    }

    /**
     * @param string $backLinkText
     * @return VehicleViewModel
     */
    public function setBackLinkText($backLinkText)
    {
        $this->backLinkText = $backLinkText;
        return $this;
    }

    /**
     * @return string
     */
    public function getPageSecondaryTitle()
    {
        return $this->pageSecondaryTitle;
    }

    /**
     * @param string $pageSecondaryTitle
     * @return VehicleViewModel
     */
    public function setPageSecondaryTitle($pageSecondaryTitle)
    {
        $this->pageSecondaryTitle = $pageSecondaryTitle;
        return $this;
    }

    /**
     * @return HeaderTertiaryList
     */
    public function getPageTertiaryTitle()
    {
        return $this->pageTertiaryTitle;
    }

    /**
     * @param HeaderTertiaryList $pageTertiaryTitle
     * @return VehicleViewModel
     */
    public function setPageTertiaryTitle($pageTertiaryTitle)
    {
        $this->pageTertiaryTitle = $pageTertiaryTitle;
        return $this;
    }

    /**
     * @return VehicleSidebar
     */
    public function getSidebar()
    {
        return $this->sidebar;
    }

    /**
     * @param VehicleSidebar $sidebar
     * @return VehicleViewModel
     */
    public function setSidebar($sidebar)
    {
        $this->sidebar = $sidebar;
        return $this;
    }

    /**
     * @return array
     */
    public function getBreadcrumbs()
    {
        return $this->breadcrumbs;
    }

    /**
     * @param array $breadcrumbs
     * @return VehicleViewModel
     */
    public function setBreadcrumbs($breadcrumbs)
    {
        $this->breadcrumbs = $breadcrumbs;
        return $this;
    }
}