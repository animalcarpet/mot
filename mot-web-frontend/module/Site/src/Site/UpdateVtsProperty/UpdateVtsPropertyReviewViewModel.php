<?php

namespace Site\UpdateVtsProperty;

use Core\ViewModel\Gds\Table\GdsTable;

class UpdateVtsPropertyReviewViewModel
{
    private $submitButtonText;
    private $formData;
    private $vtsId;
    private $summary;
    private $propertyName;
    private $formUuid;

    public function __construct($vtsId, $propertyName, $formUuid, $submitButtonText, $formData, GdsTable $summary)
    {
        $this->submitButtonText = $submitButtonText;
        $this->formData = $formData;
        $this->vtsId = $vtsId;
        $this->summary = $summary;
        $this->propertyName = $propertyName;
        $this->formUuid = $formUuid;
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    /**
     * @return
     *
     * @deprecated Check if this exists, if not remove it
     */
    public function getFormData()
    {
        return $this->formData;
    }

    public function getVtsId()
    {
        return $this->vtsId;
    }

    public function getSummary()
    {
        return $this->summary;
    }

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getFormUuid()
    {
        return $this->formUuid;
    }
}
