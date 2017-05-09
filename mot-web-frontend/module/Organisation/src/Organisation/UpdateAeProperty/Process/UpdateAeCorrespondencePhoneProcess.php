<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\Process\Form\CorrespondencePhonePropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeCorrespondencePhoneProcess extends UpdateAeRegisteredPhoneProcess
{
    protected $propertyName = UpdateAePropertyAction::AE_CORRESPONDENCE_TELEPHONE_PROPERTY;
    protected $permission = PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_PHONE;
    protected $requiresReview = false;
    protected $successfulEditMessage = 'Correspondence telephone number has been successfully changed.';
    protected $submitButtonText = 'Change correspondence telephone number';
    protected $formPageTitle = 'Change correspondence telephone number';

    public function createEmptyForm()
    {
        return new CorrespondencePhonePropertyForm();
    }

    public function getPrePopulatedData()
    {
        $contact = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId())->getContactByType(OrganisationContactTypeCode::CORRESPONDENCE);

        return [$this->propertyName => $contact->getPrimaryPhoneNumber()];
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::CORRESPONDENCE_PHONE, $formData[$this->propertyName]);
    }
}
