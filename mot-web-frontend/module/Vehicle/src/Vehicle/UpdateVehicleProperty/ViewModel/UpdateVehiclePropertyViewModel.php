<?php

namespace Vehicle\UpdateVehicleProperty\ViewModel;

use Core\ViewModel\Header\HeaderTertiaryList;
use Zend\Form\Form;

class UpdateVehiclePropertyViewModel
{
    private $form;

    /**
     * @var string
     */
    private $submitButtonText;

    /**
     * @var string
     */
    private $partial;

    /**
     * @var string
     */
    private $backUrl;

    /**
     * @var string
     */
    private $backLinkLabel = "Cancel and return to vehicle";

    /**
     * @var string
     */
    private $formActionUrl;

    /**
     * @var HeaderTertiaryList
     */
    private $pageTertiaryTitle;

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
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
    public function getPartial()
    {
        return $this->partial;
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
    public function getBackLinkText()
    {
        return $this->backLinkLabel;
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->formActionUrl;
    }

    /**
     * @param mixed $form
     * @return UpdateVehiclePropertyViewModel
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }

    /**
     * @param string $submitButtonText
     * @return UpdateVehiclePropertyViewModel
     */
    public function setSubmitButtonText($submitButtonText)
    {
        $this->submitButtonText = $submitButtonText;
        return $this;
    }

    /**
     * @param string $partial
     * @return UpdateVehiclePropertyViewModel
     */
    public function setPartial($partial)
    {
        $this->partial = $partial;
        return $this;
    }

    /**
     * @param string $backUrl
     * @return UpdateVehiclePropertyViewModel
     */
    public function setBackUrl($backUrl)
    {
        $this->backUrl = $backUrl;
        return $this;
    }

    /**
     * @param string $backLinkLabel
     * @return UpdateVehiclePropertyViewModel
     */
    public function setBackLinkText($backLinkLabel)
    {
        $this->backLinkLabel = $backLinkLabel;
        return $this;
    }

    /**
     * @param string $formActionUrl
     * @return UpdateVehiclePropertyViewModel
     */
    public function setFormActionUrl($formActionUrl)
    {
        $this->formActionUrl = $formActionUrl;
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
     * @return UpdateVehiclePropertyViewModel
     */
    public function setPageTertiaryTitle($pageTertiaryTitle)
    {
        $this->pageTertiaryTitle = $pageTertiaryTitle;
        return $this;
    }
}