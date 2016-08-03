<?php

namespace Site\UpdateVtsProperty\Process;

use Core\Formatting\AddressFormatter;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Dto\Contact\AddressDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use DvsaCommon\Utility\DtoHydrator;
use Site\UpdateVtsProperty\AbstractTwoStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\AddressPropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;

class UpdateVtsAddressProcess extends AbstractTwoStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_ADDRESS_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_ADDRESS;
    private $submitButtonText = "Review address";
    private $successfulEditMessage = "Address has been successfully changed.";
    private $formPageTitle = "Change address";
    private $formPartial = "site/update-vts-property/partials/edit-address";
    private $reviewPageTitle = "Review address";
    private $reviewPageLede = "Please check the address below is correct.";
    private $reviewPageButtonText = "Change address";

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
        return new AddressPropertyForm();
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());
        $contact = $vtsData->getContactByType(SiteContactTypeCode::BUSINESS);
        if (empty($contact)) {
            return [];
        }

        return [
            'town'          => $contact->getAddress()->getTown(),
            'postcode'      => $contact->getAddress()->getPostcode(),
            'address_line1' => $contact->getAddress()->getAddressLine1(),
            'address_line2' => $contact->getAddress()->getAddressLine2(),
            'address_line3' => $contact->getAddress()->getAddressLine3(),
        ];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $addressDto = new AddressDto();
        $addressDto->setAddressLine1($formData['address_line1']);
        $addressDto->setAddressLine2($formData['address_line2']);
        $addressDto->setAddressLine3($formData['address_line3']);
        $addressDto->setPostcode($formData['postcode']);
        $addressDto->setTown($formData['town']);
        $this->siteMapper->updateVtsContactProperty(
            $this->context->getVtsId(),
            VehicleTestingStation::PATCH_PROPERTY_ADDRESS,
            DtoHydrator::dtoToJson($addressDto)
        );
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
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());
        $table->newRow()->setLabel('Vehicle Testing Station')->setValue($vtsData->getName());
        $table->newRow("address")->setLabel("Address")
            ->setValue((new AddressFormatter())->escapeAddressToMultiLine(
                $formData['address_line1'],
                $formData['address_line2'],
                $formData['address_line3'],
                null,
                null,
                $formData['town'],
                $formData['postcode']
            ), false);

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

    public function getEditPageLede()
    {
        return null;
    }
}
