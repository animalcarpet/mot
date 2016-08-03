<?php
namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates;


use Core\ViewModel\Gds\Table\GdsTable;

class AnnualAssessmentCertificatesAddEditReviewViewModel
{
    /** @var  GdsTable $table */
    private $table;
    private $formData;
    private $backUrl;
    private $submitButtonText;

    public function __construct(
        $formData,
        GdsTable $table,
        $backUrl,
        $submitButtonText
    )
    {
        $this->formData = $formData;
        $this->table = $table;
        $this->backUrl = $backUrl;
        $this->submitButtonText = $submitButtonText;
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
    public function getFormData()
    {
        return $this->formData;
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->backUrl;
    }

    /**
     * @return string
     */
    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }
}