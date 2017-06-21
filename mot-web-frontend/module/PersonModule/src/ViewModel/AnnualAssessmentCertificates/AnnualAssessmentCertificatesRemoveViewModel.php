<?php

namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates;

use Core\ViewModel\Gds\Table\GdsTable;

class AnnualAssessmentCertificatesRemoveViewModel
{
    private $table;
    private $editStepPageTitle;
    private $pageSubTitle;
    private $submitButtonText;
    private $backRoute;
    private $backRouteParams;
    private $backRouteQueryParams;

    public function __construct(
        GdsTable $table,
        $editStepPageTitle,
        $pageSubTitle,
        $submitButtonText,
        $backRoute,
        $backRouteParams,
        array $backRouteQueryParams = []
    ) {
        $this->table = $table;
        $this->editStepPageTitle = $editStepPageTitle;
        $this->pageSubTitle = $pageSubTitle;
        $this->submitButtonText = $submitButtonText;
        $this->backRoute = $backRoute;
        $this->backRouteParams = $backRouteParams;
        $this->backRouteQueryParams = $backRouteQueryParams;
    }

    /**
     * @return GdsTable
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getEditStepPageTitle()
    {
        return $this->editStepPageTitle;
    }

    /**
     * @return string
     */
    public function getPageSubTitle()
    {
        return $this->pageSubTitle;
    }

    /**
     * @return string
     */
    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    /**
     * @return string
     */
    public function getBackRoute()
    {
        return $this->backRoute;
    }

    /**
     * @return string
     */
    public function getBackRouteParams()
    {
        return $this->backRouteParams;
    }

    /**
     * @return array
     */
    public function getBackRouteQueryParams()
    {
        return $this->backRouteQueryParams;
    }
}
