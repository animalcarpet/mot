<?php

namespace Organisation\UpdateAeProperty\Process;

use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\Process\Form\AddressPropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;

class UpdateAeCorrespondenceAddressProcess extends UpdateAeRegisteredAddressProcess
{
    protected $propertyName = UpdateAePropertyAction::AE_CORRESPONDENCE_ADDRESS_PROPERTY;
    protected $permission = PermissionAtOrganisation::AE_UPDATE_CORRESPONDENCE_ADDRESS;
    protected $successfulEditMessage = 'Correspondence address has been successfully changed.';
    protected $breadcrumbLabel = 'Change correspondence address';
    protected $submitButtonText = 'Review correspondence address';
    protected $formPageTitle = 'Change correspondence address';
    protected $reviewPageTitle = 'Review correspondence address';
    protected $reviewPageButtonText = 'Change correspondence address';

    public function getPrePopulatedData()
    {
        $authorisedExaminer = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId());
        $contact = $authorisedExaminer->getContactByType(OrganisationContactTypeCode::CORRESPONDENCE);
        if (empty($contact) || empty($contact->getAddress())) {
            return [];
        }
        $address = $contact->getAddress();

        return $this->prepopulateFromAddressDto($address);
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAePropertiesWithArray($this->context->getAeId(), [
            AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_POSTCODE => $formData[AddressPropertyForm::FIELD_POSTCODE],
            AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_COUNTRY => $formData[AddressPropertyForm::FIELD_COUNTRY],
            AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_1 => $formData[AddressPropertyForm::FIELD_ADDRESS_LINE_1],
            AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_2 => $formData[AddressPropertyForm::FIELD_ADDRESS_LINE_2],
            AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_LINE_3 => $formData[AddressPropertyForm::FIELD_ADDRESS_LINE_3],
            AuthorisedExaminerPatchModel::CORRESPONDENCE_ADDRESS_TOWN => $formData[AddressPropertyForm::FIELD_TOWN],
        ]);
    }
}
