<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel;

use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Form\SecurityCardActivationForm;

class RegisterCardViewModel
{
    /**
     * @var SecurityCardActivationForm
     */
    private $form;

    /** @var array */
    private $gtmData = [];

    /** @var bool */
    private $pinMismatch = false;

    /** @var bool */
    private $invalidSerialNumber = false;

    /** @var bool */
    private $cardAlreadyRegistered = false;

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
     * @return array
     */
    public function getGtmData()
    {
        return $this->gtmData;
    }

    /**
     * @param array $gtmData
     * @return RegisterCardViewModel
     */
    public function setGtmData(array $gtmData)
    {
        $this->gtmData = $gtmData;

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
     * @return boolean
     */
    public function isInvalidSerialNumber()
    {
        return $this->invalidSerialNumber;
    }

    /**
     * @param boolean $invalidSerialNumber
     * @return RegisterCardViewModel
     */
    public function setInvalidSerialNumber($invalidSerialNumber)
    {
        $this->invalidSerialNumber = $invalidSerialNumber;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isCardAlreadyRegistered()
    {
        return $this->cardAlreadyRegistered;
    }

    /**
     * @param boolean $cardAlreadyRegistered
     * @return RegisterCardViewModel
     */
    public function setCardAlreadyRegistered($cardAlreadyRegistered)
    {
        $this->cardAlreadyRegistered = $cardAlreadyRegistered;

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
