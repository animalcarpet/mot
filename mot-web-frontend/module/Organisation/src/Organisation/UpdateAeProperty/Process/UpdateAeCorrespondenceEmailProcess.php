<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\Process\Form\CorrespondenceEmailPropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeCorrespondenceEmailProcess extends UpdateAeRegisteredEmailProcess
{
    protected $propertyName = UpdateAePropertyAction::AE_CORRESPONDENCE_EMAIL_PROPERTY;
    protected $permission = PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_EMAIL;
    protected $successfulEditMessage = 'Correspondence email address has been successfully changed.';
    protected $submitButtonText = 'Change correspondence email address';
    protected $formPageTitle = 'Change correspondence email address';

    public function createEmptyForm()
    {
        return new CorrespondenceEmailPropertyForm();
    }

    public function getPrePopulatedData()
    {
        $contact = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId())->getContactByType(OrganisationContactTypeCode::CORRESPONDENCE);

        return [$this->propertyName => $contact->getPrimaryEmailAddress()];
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::CORRESPONDENCE_EMAIL, $formData[$this->propertyName]);
    }

    public function getEditPageLede()
    {
        return null;
    }
}
