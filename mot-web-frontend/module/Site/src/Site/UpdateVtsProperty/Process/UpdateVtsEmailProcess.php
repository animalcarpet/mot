<?php

namespace Site\UpdateVtsProperty\Process;

use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use Site\UpdateVtsProperty\AbstractSingleStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\EmailPropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;

class UpdateVtsEmailProcess extends AbstractSingleStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_EMAIL_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_EMAIL;
    private $submitButtonText = 'Change email address';
    private $successfulEditMessage = 'Email address has been successfully changed.';
    private $formPageTitle = 'Change email address';
    private $formPartial = 'site/update-vts-property/partials/edit-email';

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
        return new EmailPropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $contact = $this->siteMapper->getById($this->context->getVtsId())->getContactByType(SiteContactTypeCode::BUSINESS);

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
        $this->siteMapper->updateVtsContactProperty($this->context->getVtsId(), VehicleTestingStation::PATCH_PROPERTY_EMAIL, $formData[$this->propertyName]);
    }

    public function getEditPageLede()
    {
        return null;
    }
}
