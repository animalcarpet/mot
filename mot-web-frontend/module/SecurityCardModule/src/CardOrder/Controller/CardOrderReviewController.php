<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action\CardOrderReviewAction;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;

class CardOrderReviewController extends AbstractDvsaActionController
{
    /**
     * @var OrderNewSecurityCardSessionService
     */
    protected $session;

    private $action;

    private $identity;

    /**
     * CardOrderReviewController constructor.
     * @param OrderNewSecurityCardSessionService $securityCardSessionService
     * @param CardOrderReviewAction $reviewAction
     */
    public function __construct(
        OrderNewSecurityCardSessionService $securityCardSessionService,
        CardOrderReviewAction $reviewAction,
        Identity $identity
    )
    {
        $this->session = $securityCardSessionService;
        $this->action = $reviewAction;
        $this->identity = $identity;
    }

    public function indexAction()
    {
        $this->buildBreadcrumbs();
        $userId = $this->params()->fromRoute('userId', $this->identity->getUserId());

        $result = $this->action->execute($this->request, $userId);
        return $this->applyActionResult($result);
    }

    private function buildBreadcrumbs()
    {
        $this->getBreadcrumbBuilder()
            ->simple('Your profile', 'newProfile')
            ->simple('Order a security card')
            ->build();
    }
}
