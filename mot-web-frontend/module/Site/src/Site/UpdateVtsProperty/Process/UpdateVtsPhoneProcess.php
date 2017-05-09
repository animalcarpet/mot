<?php

namespace Site\UpdateVtsProperty\Process;

use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use Site\UpdateVtsProperty\AbstractSingleStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\PhonePropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;

class UpdateVtsPhoneProcess extends AbstractSingleStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_PHONE_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_PHONE;
    private $submitButtonText = 'Change telephone number';
    private $successfulEditMessage = 'Telephone has been successfully changed.';
    private $formPageTitle = 'Change telephone number';
    private $formPartial = 'site/update-vts-property/partials/edit-phone';

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
        return new PhonePropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());

        $contactDto = $vtsData->getContactByType(SiteContactTypeCode::BUSINESS);

        $phoneNumber = $contactDto ? $contactDto->getPrimaryPhoneNumber() : '';

        return [$this->propertyName => $phoneNumber];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->siteMapper->updateVtsContactProperty($this->context->getVtsId(), VehicleTestingStation::PATCH_PROPERTY_PHONE, $formData[$this->propertyName]);
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
