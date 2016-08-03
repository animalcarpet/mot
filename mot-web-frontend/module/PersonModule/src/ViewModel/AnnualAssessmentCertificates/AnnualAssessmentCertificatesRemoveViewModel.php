<?php
namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates;

use Core\ViewModel\Gds\Table\GdsTable;
use PersonModule\src\Form\AnnualAssessmentCertificatesRemoveForm;

class AnnualAssessmentCertificatesRemoveViewModel
{
    private $table;
    private $editStepPageTitle;
    private $pageSubTitle;
    private $submitButtonText;
    private $backRoute;
    private $backRouteParams;

    public function __construct(
        GdsTable $table,
        $editStepPageTitle,
        $pageSubTitle,
        $submitButtonText,
        $backRoute,
        $backRouteParams
    ) {

        $this->table = $table;
        $this->editStepPageTitle = $editStepPageTitle;
        $this->pageSubTitle = $pageSubTitle;
        $this->submitButtonText = $submitButtonText;
        $this->backRoute = $backRoute;
        $this->backRouteParams = $backRouteParams;
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
}