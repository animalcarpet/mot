<?php

namespace Site\UpdateVtsProperty\Process;

use Core\Catalog\Vts\VtsTypeCatalog;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use Site\UpdateVtsProperty\AbstractSingleStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\TypePropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\View\Helper\Url;

class UpdateVtsTypeProcess extends AbstractSingleStepVtsProcess implements AutoWireableInterface
{
    private $propertyName = UpdateVtsPropertyAction::VTS_TYPE_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_TYPE;
    private $submitButtonText = "Change site type";
    private $successfulEditMessage = "Site type has been successfully changed.";
    private $formPageTitle = "Change site type";
    private $formPartial = "site/update-vts-property/partials/edit-type";
    private $vtsTypeCatalog;

    public function __construct(SiteMapper $siteMapper, VtsTypeCatalog $vtsTypeCatalog, Url $urlHelper)
    {
        parent::__construct($siteMapper, $urlHelper);
        $this->vtsTypeCatalog = $vtsTypeCatalog;
    }

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
        return new TypePropertyForm($this->vtsTypeCatalog);
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());
        return [$this->propertyName => $vtsData->getType()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->siteMapper->updateVtsProperty($this->context->getVtsId(), VehicleTestingStation::PATCH_PROPERTY_TYPE, $formData[$this->propertyName]);
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
