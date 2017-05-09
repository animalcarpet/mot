<?php

namespace Site\UpdateVtsProperty\Process;

use Core\Catalog\CountryCatalog;
use Core\ViewModel\Gds\Table\GdsTable;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Enum\CountryCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\VehicleTestingStation;
use Site\UpdateVtsProperty\AbstractSingleStepVtsProcess;
use Site\UpdateVtsProperty\Process\Form\CountryPropertyForm;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\View\Helper\Url;

class UpdateVtsCountryProcess extends AbstractSingleStepVtsProcess implements AutoWireableInterface
{
    private $breadcrumbLabel = 'Change site country';
    private $propertyName = UpdateVtsPropertyAction::VTS_COUNTRY_PROPERTY;
    private $permission = PermissionAtSite::VTS_UPDATE_COUNTRY;
    private $submitButtonText = 'Change country';
    private $successfulEditMessage = 'Country has been successfully changed.';
    private $formPageTitle = 'Change country';
    private $formPartial = 'site/update-vts-property/partials/edit-country';
    private $reviewPageTitle = '';
    private $reviewPageLede = '';
    private $reviewPageButtonText = '';

    /**
     * @var CountryCatalog
     */
    private $countryCatalog;

    public function __construct(SiteMapper $siteMapper, CountryCatalog $vtsCountryCatalog, Url $urlHelper)
    {
        parent::__construct($siteMapper, $urlHelper);
        $this->countryCatalog = $vtsCountryCatalog;
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
        return new CountryPropertyForm($this->countryCatalog);
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $vtsData = $this->siteMapper->getById($this->context->getVtsId());
        $country = CountryCode::ENGLAND;

        if ($vtsData->isDualLanguage()) {
            $country = CountryCode::WALES;
        } elseif ($vtsData->isScottishBankHoliday()) {
            $country = CountryCode::SCOTLAND;
        }

        return [$this->propertyName => $country];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->siteMapper->updateVtsProperty(
            $this->context->getVtsId(),
            VehicleTestingStation::PATCH_PROPERTY_COUNTRY,
            $formData[$this->propertyName]
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

    public function transformFormIntoGdsTable($vtsId, array $formData)
    {
        $table = new GdsTable();

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

    public function getBreadcrumbLabel()
    {
        return $this->breadcrumbLabel;
    }

    public function getEditPageLede()
    {
        return null;
    }
}
