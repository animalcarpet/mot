<?php

namespace Site\UpdateVtsProperty\Process;

use Core\ViewModel\Gds\Table\GdsTable;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use DvsaCommon\Utility\ArrayUtils;
use Site\UpdateVtsProperty\AbstractTwoStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\ClassesPropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;

class UpdateVtsClassesReviewProcess extends AbstractTwoStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_CLASSES_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_CLASSES;
    private $submitButtonText = 'Review classes';
    private $successfulEditMessage = 'Classes have been successfully changed.';
    private $formPageTitle = 'Change classes';
    private $formPartial = 'site/update-vts-property/partials/edit-classes';
    private $reviewPageTitle = 'Review classes';
    private $reviewPageLede = 'Please check the classes below are correct.';
    private $reviewPageButtonText = 'Change classes';

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
        return new ClassesPropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());

        return [$this->propertyName => $vtsData->getTestClasses()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $patchData = ArrayUtils::map($formData[$this->propertyName], function ($classAsString) {
            return (int) $classAsString;
        });

        $this->siteMapper->updateVtsProperty($this->context->getVtsId(), VehicleTestingStation::PATCH_PROPERTY_CLASSES, $patchData);
    }

    public function getSuccessfulEditMessage()
    {
        return $this->successfulEditMessage;
    }

    public function getEditStepPageTitle()
    {
        return $this->formPageTitle;
    }

    public function transformFormIntoGdsTable(array $formData)
    {
        $table = new GdsTable();

        $classesData = $formData[$this->propertyName];
        $classesAsText = $classesData ? implode(', ', $classesData) : 'None';

        $table->newRow()->setLabel('Vehicle Testing Station')->setValue($this->siteMapper->getById($this->context->getVtsId())->getName());
        $table->newRow($this->propertyName)->setLabel('Classes')->setValue($classesAsText);

        return $table;
    }

    public function getReviewPageTitle()
    {
        return $this->reviewPageTitle;
    }

    public function getReviewPageLede()
    {
        return $this->reviewPageLede;
    }

    public function getReviewPageButtonText()
    {
        return $this->reviewPageButtonText;
    }

    /**
     * @return mixed
     */
    public function populateConfirmationPageVariables()
    {
        // TODO: Implement populateConfirmationPageVariables() method.
    }

    public function getEditPageLede()
    {
        return null;
    }
}
