<?php

namespace Site\UpdateVtsProperty;

use Core\Action\RedirectToRoute;
use Core\Routing\VtsRouteList;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaClient\Mapper\SiteMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Utility\TypeCheck;
use Zend\View\Helper\Url;

abstract class AbstractSingleStepVtsProcess implements SingleStepProcessInterface
{
    protected $siteMapper;
    private $urlHelper;

    /**
     * @var UpdateVtsContext
     */
    protected $context;

    public function __construct(SiteMapper $siteMapper, Url $urlHelper)
    {
        $this->siteMapper = $siteMapper;
        $this->urlHelper = $urlHelper;
    }

    public function setContext(FormContextInterface $context)
    {
        TypeCheck::assertInstance($context, UpdateVtsContext::class);
        $this->context = $context;
    }

    public function getBreadcrumbs(MotAuthorisationServiceInterface $authorisationService)
    {
        $updateVtsPropertyBreadcrumbs = new UpdateVtsPropertyBreadcrumbs(
            $this->siteMapper->getById($this->context->getVtsId()),
            $authorisationService,
            $this->urlHelper,
            $this->getEditStepPageTitle()
        );

        $breadcrumbs = $updateVtsPropertyBreadcrumbs->create();

        return $breadcrumbs;
    }

    public function getPageSubTitle()
    {
        return 'Vehicle Testing Station';
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

    public function isAuthorised(MotAuthorisationServiceInterface $authorisationService)
    {
        return $authorisationService->isGrantedAtSite($this->getPermission(), $this->context->getVtsId());
    }

    abstract public function getPermission();

    abstract public function getFormPartial();

    abstract public function getPropertyName();

    public function redirectToEditPage()
    {
        return new RedirectToRoute(VtsRouteList::VTS_EDIT_PROPERTY, ['id' => $this->context->getVtsId(), 'propertyName' => $this->context->getPropertyName()]);
    }
}
