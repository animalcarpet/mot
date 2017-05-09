<?php

namespace DvsaMotTest\ViewModel\VehicleSearch;

use DvsaMotTest\Form\VehicleSearch\AbstractDuplicateCertificateSearchForm;

class DuplicateCertificateSearchViewModel
{
    /**
     * @var AbstractDuplicateCertificateSearchForm
     */
    private $form;

    /**
     * @var bool
     */
    private $showNoResultsMessage;

    /**
     * @return AbstractDuplicateCertificateSearchForm
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param AbstractDuplicateCertificateSearchForm $form
     *
     * @return DuplicateCertificateSearchViewModel
     */
    public function setForm($form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * @return bool
     */
    public function getShowNoResultsMessage()
    {
        return $this->showNoResultsMessage;
    }

    /**
     * @param bool $showNoResultsMessage
     *
     * @return DuplicateCertificateSearchViewModel
     */
    public function setShowNoResultsMessage($showNoResultsMessage)
    {
        $this->showNoResultsMessage = $showNoResultsMessage;

        return $this;
    }
}
