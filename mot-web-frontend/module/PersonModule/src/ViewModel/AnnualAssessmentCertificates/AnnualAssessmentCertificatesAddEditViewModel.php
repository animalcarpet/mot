<?php
namespace Dvsa\Mot\Frontend\PersonModule\ViewModel\AnnualAssessmentCertificates;


use Dvsa\Mot\Frontend\PersonModule\Form\AnnualAssessmentCertificatesForm;

class AnnualAssessmentCertificatesAddEditViewModel
{
    /** @var AnnualAssessmentCertificatesForm $form */
    private $form;
    private $backUrl;
    private $submitButtonText;

    public function __construct(
        AnnualAssessmentCertificatesForm $form,
        $backUrl,
        $submitButtonText
    )
    {
        $this->form = $form;
        $this->backUrl = $backUrl;
        $this->submitButtonText = $submitButtonText;
    }

    /**
     * @return AnnualAssessmentCertificatesForm
     */
    public function getForm()
    {
        return $this->form;
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