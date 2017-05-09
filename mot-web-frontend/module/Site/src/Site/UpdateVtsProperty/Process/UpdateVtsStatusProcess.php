<?php

namespace Site\UpdateVtsProperty\Process;

use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use Site\UpdateVtsProperty\AbstractSingleStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\StatusPropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;

class UpdateVtsStatusProcess extends AbstractSingleStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_STATUS_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_STATUS;
    private $submitButtonText = 'Change site status';
    private $successfulEditMessage = 'Site status has been successfully changed.';
    private $formPageTitle = 'Change status';
    private $formPartial = 'site/update-vts-property/partials/edit-status';

    public function getPropertyName()
    {
        return $this->propertyName;
    }

    public function getFormPartial()
    {
        return $this->formPartial;
    }

    public function createEmptyForm()
    {
        return new StatusPropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());

        return [$this->propertyName => $vtsData->getStatus()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->siteMapper->updateVtsProperty($this->context->getVtsId(), VehicleTestingStation::PATCH_PROPERTY_STATUS, $formData[$this->propertyName]);
    }

    public function getSuccessfulEditMessage()
    {
        return $this->successfulEditMessage;
    }

    public function getEditStepPageTitle()
    {
        return $this->formPageTitle;
    }

    public function getEditPageLede()
    {
        return null;
    }
}
