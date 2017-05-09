<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\AbstractSingleStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\AreaOfficePropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeAreaOfficeProcess extends AbstractSingleStepAeProcess implements AutoWireableInterface
{
    private $propertyName = UpdateAePropertyAction::AE_DVSA_AREA_OFFICE_STATUS_PROPERTY;
    private $permission = PermissionAtOrganisation::AE_UPDATE_DVSA_AREA_OFFICE;
    private $submitButtonText = 'Change area office';
    private $successfulEditMessage = 'Area office has been successfully changed.';
    private $formPageTitle = 'Change area office';
    private $formPartial = 'organisation/update-ae-property/partials/edit-areaoffice';

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
        return new AreaOfficePropertyForm($this->organisationMapper->getAllAreaOffices(true));
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $aeData = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId());

        return [$this->propertyName => ltrim($aeData->getAuthorisedExaminerAuthorisation()->getAssignedAreaOffice()->getSiteNumber(), '0')];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::AREA_OFFICE, $formData[$this->propertyName]);
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
