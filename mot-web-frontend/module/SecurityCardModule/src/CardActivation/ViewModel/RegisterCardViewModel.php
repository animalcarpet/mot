<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form\SecurityCardActivationForm;

class RegisterCardViewModel
{
    /**
     * @var SecurityCardActivationForm
     */
    private $form;

    /** @var bool */
    private $pinMismatch = false;

    private $skipCtaTemplate;

    public function __construct()
    {
        $this->form = new SecurityCardActivationForm();
    }

    /**
     * @return SecurityCardActivationForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param SecurityCardActivationForm $form
     * @return RegisterCardViewModel
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
    /**
     * @return boolean
     */
    public function isPinMismatch()
    {
        return $this->pinMismatch;
    }

    /**
     * @param boolean $pinMismatch
     * @return RegisterCardViewModel
     */
    public function setPinMismatch($pinMismatch)
    {
        $this->pinMismatch = $pinMismatch;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSkipCtaTemplate()
    {
        return $this->skipCtaTemplate;
    }

    /**
     * @param mixed $skipCtaTemplate
     * @return RegisterCardViewModel
     */
    public function setSkipCtaTemplate($skipCtaTemplate)
    {
        $this->skipCtaTemplate = $skipCtaTemplate;
        return $this;
    }
}