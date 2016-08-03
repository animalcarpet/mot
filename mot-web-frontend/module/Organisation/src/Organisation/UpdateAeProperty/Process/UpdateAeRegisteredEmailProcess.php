<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\AbstractSingleStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\RegisteredEmailPropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeRegisteredEmailProcess extends AbstractSingleStepAeProcess implements AutoWireableInterface
{
    protected $propertyName = UpdateAePropertyAction::AE_REGISTERED_EMAIL_PROPERTY;
    protected $permission = PermissionAtOrganisation::AE_UPDATE_REGISTERED_OFFICE_EMAIL;
    protected $requiresReview = false;
    protected $submitButtonText = "Change registered office email address";
    protected $successfulEditMessage = "Registered office email address has been successfully changed.";
    protected $formPageTitle = "Change registered office email address";
    protected $formPartial = "organisation/update-ae-property/partials/edit-email";

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
        return new RegisteredEmailPropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $contact = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId())->getContactByType(OrganisationContactTypeCode::REGISTERED_COMPANY);

        return [$this->propertyName => $contact->getPrimaryEmailAddress()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function getSuccessfulEditMessage()
    {
        return $this->successfulEditMessage;
    }

    public function getEditStepPageTitle()
    {
        return $this->formPageTitle;
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::REGISTERED_EMAIL, $formData[$this->propertyName]);
    }

    public function getEditPageLede()
    {
        return null;
    }
}
