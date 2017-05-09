<?php

namespace Site\ViewModel\VehicleTestingStation;

use Site\Form\VtsCreateForm;
use Zend\View\Model\ViewModel;

class VtsFormViewModel extends ViewModel
{
    /**
     * @var VtsCreateForm
     */
    private $form;

    private $cancelUrl;

    /**
     * @return VtsCreateForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param VtsCreateForm $form
     *
     * @return $this
     */
    public function setForm($form)
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
     * @return $this
     */
    public function setCancelUrl($url)
    {
        $this->cancelUrl = $url;

        return $this;
    }
}
