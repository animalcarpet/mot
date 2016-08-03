<?php
namespace Organisation\UpdateAeProperty\Process;

use Core\Catalog\Authorisation\AuthForAuthorisedExaminerStatusCatalog;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Model\AuthorisedExaminerPatchModel;
use Organisation\UpdateAeProperty\AbstractSingleStepAeProcess;
use Organisation\UpdateAeProperty\Process\Form\StatusPropertyForm;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\View\Helper\Url;

class UpdateAeStatusProcess extends AbstractSingleStepAeProcess implements AutoWireableInterface
{
    private $propertyName = UpdateAePropertyAction::AE_STATUS_PROPERTY;
    private $permission = PermissionAtOrganisation::AE_UPDATE_STATUS;
    private $submitButtonText = "Change status";
    private $successfulEditMessage = "Status has been successfully changed.";
    private $formPageTitle = "Change status";
    private $formPartial = "organisation/update-ae-property/partials/edit-status";

    /**
     * @var AuthForAuthorisedExaminerStatusCatalog
     */
    private $authForAuthorisedExaminerStatusCatalog;
    /**
     * @var MapperFactory
     */
    private $mapper;

    public function __construct(
        OrganisationMapper $organisationMapper,
        AuthForAuthorisedExaminerStatusCatalog $authForAuthorisedExaminerStatusCatalog,
        MapperFactory $mapper,
        Url $url
    )
    {
        parent::__construct($organisationMapper, $url);
        $this->authForAuthorisedExaminerStatusCatalog = $authForAuthorisedExaminerStatusCatalog;
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
        return new StatusPropertyForm($this->authForAuthorisedExaminerStatusCatalog);
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $aeData = $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId());
        return [$this->propertyName => $aeData->getAuthorisedExaminerAuthorisation()->getStatus()->getCode()];
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $this->organisationMapper->updateAeProperty($this->context->getAeId(), AuthorisedExaminerPatchModel::STATUS, $formData[$this->propertyName]);
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
