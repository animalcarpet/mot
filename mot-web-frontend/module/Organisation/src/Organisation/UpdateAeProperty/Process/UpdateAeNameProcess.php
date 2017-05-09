<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\AbstractSingleStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\NamePropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeNameProcess extends AbstractSingleStepAeProcess implements AutoWireableInterface
{
    private $propertyName = UpdateAePropertyAction::AE_NAME_PROPERTY;
    private $permission = PermissionAtOrganisation::AE_UPDATE_NAME;
    private $submitButtonText = 'Change business name';
    private $successfulEditMessage = 'Business name has been successfully changed.';
    private $formPageTitle = 'Change business name';
    private $formPartial = 'organisation/update-ae-property/partials/edit-name';

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
        $aeData = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId());

        return [$this->propertyName => $aeData->getName()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::NAME, $formData[$this->propertyName]);
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
