<?php

namespace Site\UpdateVtsProperty\Process;

use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use Site\UpdateVtsProperty\AbstractSingleStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\NamePropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;

class UpdateVtsNameProcess extends AbstractSingleStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_NAME_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_NAME;
    private $submitButtonText = 'Change site name';
    private $successfulEditMessage = 'Site name has been successfully changed.  ';
    private $formPageTitle = 'Change site name';
    private $formPartial = 'site/update-vts-property/partials/edit-name';

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
        return new NamePropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());

        return [$this->propertyName => $vtsData->getName()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->siteMapper->updateVtsProperty($this->context->getVtsId(), VehicleTestingStation::PATCH_PROPERTY_NAME, $formData[$this->propertyName]);
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
