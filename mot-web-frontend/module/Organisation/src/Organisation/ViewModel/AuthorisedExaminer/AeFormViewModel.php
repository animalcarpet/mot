<?php

namespace Organisation\ViewModel\AuthorisedExaminer;

use DvsaClient\ViewModel\AbstractFormModel;

class AeFormViewModel
{
    /**
     * @var AbstractFormModel
     */
    private $form;

    private $cancelUrl;

    /**
     * @return AbstractFormModel
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param AbstractFormModel $form
     *
     * @return $this
     */
    public function setForm(AbstractFormModel $form = null)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setCancelUrl($url)
    {
        $this->cancelUrl = $url;

        return $this;
    }
}
