<?php

namespace Organisation\UpdateAeProperty;

use Core\Action\RedirectToRoute;
use Core\Routing\AeRouteList;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaClient\Mapper\OrganisationMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Utility\TypeCheck;
use Zend\View\Helper\Url;

abstract class AbstractSingleStepAeProcess implements SingleStepProcessInterface
{
    protected $organisationMapper;
    private $urlHelper;

    /**
     * @var UpdateAeContext
     */
    protected $context;

    public function __construct(OrganisationMapper $organisationMapper, Url $urlHelper)
    {
        $this->organisationMapper = $organisationMapper;
        $this->urlHelper = $urlHelper;
    }

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, UpdateAeContext::class);
        $this->context = $context;
    }

    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        $updateAePropertyBreadcrumbs = new UpdateAePropertyBreadcrumbs(
            $this->organisationMapper->getAuthorisedExaminer($this->context->getAeId()),
            $authorisationService,
            $this->urlHelper,
            $this->getEditStepPageTitle()
        );

        $breadcrumbs = $updateAePropertyBreadcrumbs->create();

        return $breadcrumbs;
    }

    public function getPageSubTitle()
    {
        return 'Authorised Examiner';
    }

    public function buildEditStepViewModel($form)
    {
        return new UpdateAePropertyViewModel(
            $this->context->getAeId(), $this->context->getPropertyName(), $this->getFormPartial(), $this->getSubmitButtonText(), $form
        );
    }

    public function redirectToStartPage()
    {
        return new RedirectToRoute(AeRouteList::AE, ['id' => $this->context->getAeId()]);
    }

    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $authorisationService->isGrantedAtOrganisation($this->getPermission(), $this->context->getAeId());
    }

    abstract public function getPermission();

    abstract public function getFormPartial();

    abstract public function getPropertyName();

    public function redirectToEditPage()
    {
        return new RedirectToRoute(AeRouteList::AE_EDIT_PROPERTY, ['id' => $this->context->getAeId(), 'propertyName' => $this->context->getPropertyName()]);
    }
}
