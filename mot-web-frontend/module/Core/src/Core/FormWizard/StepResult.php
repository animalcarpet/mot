<?php

namespace Core\FormWizard;

class StepResult
{
    private $stepLayoutData;
    private $viewModel;
    private $errorMessages = [];

    public function __construct(LayoutData $stepLayoutData, $viewModel, $errorMessages, $template)
    {
        $this->stepLayoutData = $stepLayoutData;
        $this->viewModel = $viewModel;
        $this->errorMessages = $errorMessages;
        $this->template = $template;
    }

    /**
     * @return LayoutData
     */
    public function getStepLayoutData()
    {
        return $this->stepLayoutData;
    }

    /**
     * @return mixed
     */
    public function getViewModel()
    {
        return $this->viewModel;
    }

    /**
     * @return array
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
