<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\AbstractSingleStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\TradingNamePropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeTradingNameProcess extends AbstractSingleStepAeProcess implements AutoWireableInterface
{
    private $propertyName = UpdateAePropertyAction::AE_TRADING_NAME_PROPERTY;
    private $permission = PermissionAtOrganisation::AE_UPDATE_TRADING_NAME;
    private $submitButtonText = 'Change trading name';
    private $successfulEditMessage = 'Trading name has been successfully changed.';
    private $formPageTitle = 'Change trading name';
    private $formPartial = 'organisation/update-ae-property/partials/edit-trading-name';

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
        return new TradingNamePropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $aeData = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId());

        return [$this->propertyName => $aeData->getTradingAs()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::TRADING_NAME, $formData[$this->propertyName]);
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
