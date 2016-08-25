<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller;

use Core\Controller\AbstractDvsaActionController;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use Zend\Http\Request;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\ViewModel;

class AlreadyOrderedNewCardController extends AbstractDvsaActionController
{
    const ROUTE = 'security-card-order/already-ordered';

    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var SecurityCardService
     */
    private $securityCardService;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    public function __construct(
        Request $request,
        Identity $identity,
        SecurityCardService $securityCardService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    ) {
        $this->request = $request;
        $this->identity = $identity;
        $this->securityCardService = $securityCardService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    public function onDispatch(MvcEvent $e)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        return parent::onDispatch($e);
    }

    public function indexAction()
    {
        $cardOrder = $this->securityCardService->getMostRecentSecurityCardOrderForUser($this->identity->getUsername());

        if ($cardOrder === null) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        $viewModel = new ViewModel(['cardOrder' => $cardOrder]);
        $viewModel->setTemplate('2fa/card-order/already-ordered');

        return $viewModel;
    }
}
