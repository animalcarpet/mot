<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use Core\Action\ActionResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardViewModel;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

abstract class RegisterCardAction
{
    const REGISTER_PAGE_SUBTITLE = "Your profile";
    const REGISTER_SUCCESS_TITLE = "Security card activated";

    /**  @var RegisterCardViewStrategy */
    protected $viewStrategy;

    public function __construct(
        RegisterCardViewStrategy $viewStrategy
    ) {
        $this->viewStrategy = $viewStrategy;
    }

    public abstract function doExecute(Request $request);

    public function execute(Request $request)
    {
        return $this->doExecute($request);
    }

    protected function defaultActionResult()
    {
        $result = new ActionResult();
        $viewModel = new RegisterCardViewModel();
        $viewModel->setSkipCtaTemplate($this->viewStrategy->skipCtaTemplate());

        $result->setTemplate('2fa/register-card/register-card');
        $result->layout()->setPageSubTitle($this->viewStrategy->pageSubTitle());
        $result->layout()->setBreadcrumbs($this->viewStrategy->breadcrumbs());

        $result->setViewModel($viewModel);

        return $result;
    }
}
