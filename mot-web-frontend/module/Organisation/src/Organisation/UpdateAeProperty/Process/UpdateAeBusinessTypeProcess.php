<?php

namespace Organisation\UpdateAeProperty\Process;

use Core\Catalog\Organisation\OrganisationCompanyTypeCatalog;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\AbstractSingleStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\BusinessTypePropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\View\Helper\Url;

class UpdateAeBusinessTypeProcess extends AbstractSingleStepAeProcess implements AutoWireableInterface
{
    private $propertyCompanyNumber = UpdateAePropertyAction::AE_COMPANY_NUMBER_PROPERTY;
    private $propertyName = UpdateAePropertyAction::AE_BUSINESS_TYPE_PROPERTY;
    private $permission = PermissionAtOrganisation::AE_UPDATE_TYPE;
    private $submitButtonText = 'Change business type';
    private $successfulEditMessage = 'Business type has been successfully changed.';
    private $formPageTitle = 'Change business type';
    private $formPartial = 'organisation/update-ae-property/partials/edit-business-type';

    /**
     * @var OrganisationCompanyTypeCatalog
     */
    private $organisationCompanyTypeCatalog;
    /**
     * @var MapperFactory
     */
    private $mapper;

    public function __construct(
        OrganisationMapper $organisationMapper,
        OrganisationCompanyTypeCatalog $organisationCompanyTypeCatalog,
        MapperFactory $mapper,
        Url $urlHelper
    ) {
        parent::__construct($organisationMapper, $urlHelper);
        $this->organisationCompanyTypeCatalog = $organisationCompanyTypeCatalog;
        $this->mapper = $mapper;
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
        return new BusinessTypePropertyForm($this->organisationCompanyTypeCatalog);
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $authorisedExaminer = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId());
        $companyType = $this->organisationCompanyTypeCatalog->getByName($authorisedExaminer->getCompanyType());
        $return = [$this->propertyCompanyNumber => $authorisedExaminer->getRegisteredCompanyNumber()];
        if (!is_null($companyType)) {
            $return[$this->propertyName] = $companyType->getCode();
        }

        return $return;
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $companyNumber = $formData[$this->propertyCompanyNumber];
        if ($formData[$this->propertyName] != CompanyTypeCode::COMPANY) {
            return $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::TYPE,
                $formData[$this->propertyName]);
        } else {
            return $this->organisationMapper->updateAePropertiesWithArray($this->context->getAeId(), [
                AuthorisedExaminerPatchModel::TYPE => $formData[$this->propertyName],
                AuthorisedExaminerPatchModel::COMPANY_NUMBER => $companyNumber,
            ]);
        }
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
