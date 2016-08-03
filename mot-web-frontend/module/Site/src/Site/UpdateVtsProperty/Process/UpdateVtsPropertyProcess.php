<?php

namespace Site\UpdateVtsProperty\Process;

use Core\Action\RedirectToRoute;
use Core\Routing\VtsRouteList;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Site\UpdateVtsProperty\UpdateVtsContext;
use Site\UpdateVtsProperty\UpdateVtsPropertyViewModel;
use Zend\Form\Form;

/**
 *
 * todo rename this to test process
 * Class UpdateVtsReviewProcess
 * @package Site\UpdateVtsProperty\Process
 */
class UpdateVtsPropertyProcess implements SingleStepProcessInterface, AutoWireableInterface
{
    protected $propertyName;
    protected $permission;
    protected $form;
    protected $submitButtonText;
    protected $formPartial;
    protected $currentValuesExtractor;
    protected $valueUpdater;
    protected $successfulEditMessage;
    protected $formPageTitle;
    protected $breadCrumb;
    protected $pageSubTitle;

    /**
     * @var UpdateVtsContext
     */
    protected $context;

    public function __construct(
        $propertyName,
        $permission,
        $formPartial,
        Form $form,
        $submitButtonText,
        $currentValuesExtractor,
        $valueUpdater,
        $successfulEditMessage,
        $formPageTitle,
        $pageSubTitle,
        $breadCrumb
    )
    {
        $this->permission = $permission;
        $this->propertyName = $propertyName;
        $this->form = $form;
        $this->submitButtonText = $submitButtonText;
        $this->formPartial = $formPartial;
        $this->currentValuesExtractor = $currentValuesExtractor;
        $this->valueUpdater = $valueUpdater;
        $this->successfulEditMessage = $successfulEditMessage;
        $this->formPageTitle = $formPageTitle;
        $this->breadCrumb = $breadCrumb;
        $this->pageSubTitle = $pageSubTitle;
    }

    public function setContext(FormContextInterface $context)
    {
        $this->context = $context;
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
        return $this->form;
    }

    public function getSubmitButtonText()
    {
        return $this->submitButtonText;
    }

    public function getPrePopulatedData()
    {
        $function = $this->currentValuesExtractor;
        return $function($this->context->getVtsId());
    }

    public function getPermission()
    {
        return $this->permission;
    }

    public function update($formData)
    {
        $valueUpdater = $this->valueUpdater;
        $valueUpdater($this->context->getVtsId(), $formData);
    }

    public function getSuccessfulEditMessage()
    {
        return $this->successfulEditMessage;
    }

    public function getEditStepPageTitle()
    {
        return $this->formPageTitle;
    }

    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        // todo test this
        return $this->breadCrumb;
    }

    public function getPageSubTitle()
    {
        return $this->pageSubTitle;
    }

    public function buildEditStepViewModel($form)
    {
        return new UpdateVtsPropertyViewModel(
            $this->context->getVtsId(), $this->context->getPropertyName(), $this->getFormPartial(), $this->getSubmitButtonText(), $form
        );
    }

    public function redirectToStartPage()
    {
        return new RedirectToRoute(VtsRouteList::VTS, ['id' => $this->context->getVtsId()]);
    }

    public function redirectToEditPage()
    {
        return new RedirectToRoute(VtsRouteList::VTS_EDIT_PROPERTY, ['id' => $this->context->getVtsId(), 'propertyName' => $this->context->getPropertyName()]);
    }

    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $authorisationService->isGrantedAtSite($this->getPermission(), $this->context->getVtsId());
    }

    public function getEditPageLede()
    {
        return null;
    }
}
