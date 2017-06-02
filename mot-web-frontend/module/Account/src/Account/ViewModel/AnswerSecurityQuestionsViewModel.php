<?php

namespace Account\ViewModel;

use Account\Form\SecurityQuestionAnswersForm;

class AnswerSecurityQuestionsViewModel
{
    /** @var array $validationMessages */
    private $validationMessages;

    /** @var SecurityQuestionAnswersForm $form */
    private $form;

    /** @var array $helpdeskConfig */
    private $helpdeskConfig;

    /** @var Url $urlBack */
    private $urlBack;

    /**
     * @return array
     */
    public function getValidationMessages()
    {
        return $this->validationMessages;
    }

    /**
     * @param $validationMessages
     * @return $this
     */
    public function setValidationMessages($validationMessages)
    {
        $this->validationMessages = $validationMessages;

        return $this;
    }

    /**
     * @return SecurityQuestionAnswersForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param $form
     * @return $this
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return array
     */
    public function getHelpdeskConfig()
    {
        return $this->helpdeskConfig;
    }

    /**
     * @param $helpdeskConfig
     * @return $this
     */
    public function setHelpdeskConfig($helpdeskConfig)
    {
        $this->helpdeskConfig = $helpdeskConfig;

        return $this;
    }

    /**
     * @return Url
     */
    public function getUrlBack()
    {
        return $this->urlBack;
    }

    /**
     * @param $urlBack
     * @return $this
     */
    public function setUrlBack($urlBack)
    {
        $this->urlBack = $urlBack;

        return $this;
    }
}
