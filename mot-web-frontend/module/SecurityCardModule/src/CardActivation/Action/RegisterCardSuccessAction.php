<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Action;

use Core\Action\ActionResult;
use Core\Action\NotFoundActionResult;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service\RegisterCardViewStrategy;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\ViewModel\RegisterCardSuccessViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\TwoFactorNominationNotificationService;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class RegisterCardSuccessAction implements AutoWireableInterface
{
    /**
     * @var RegisterCardService
     */
    protected $registerCardService;

    /**
     * @var RegisterCardViewStrategy
     */
    protected $viewStrategy;

    /**
     * @var TwoFactorNominationNotificationService $twoFactorNominationNotificationService
     */
    protected $twoFactorNominationNotificationService;

    /**
     * @var MotFrontendIdentityProviderInterface $identityProvider
     */
    protected $identityProvider;


    public function __construct(RegisterCardService $registerCardService, RegisterCardViewStrategy $viewStrategy,
                                TwoFactorNominationNotificationService $twoFactorNominationNotificationService,
                                MotFrontendIdentityProviderInterface $identityProvider)
    {
        $this->registerCardService = $registerCardService;
        $this->viewStrategy = $viewStrategy;
        $this->twoFactorNominationNotificationService = $twoFactorNominationNotificationService;
        $this->identityProvider = $identityProvider;
    }

    public function execute(Request $request)
    {
        if (!$this->registerCardService->isUserRegistered()) {
            return new NotFoundActionResult();
        }

        return $this->defaultActionResult($request);
    }

    protected function defaultActionResult(Request $request)
    {
        $identity = $this->identityProvider->getIdentity();
        $result = new ActionResult();
        $viewModel = new RegisterCardSuccessViewModel();

        $hasPendingRoleNominations = $this->twoFactorNominationNotificationService->hasPendingNominations($identity->getUserId());
        $viewModel->setHasPendingNominations($hasPendingRoleNominations);

        $newlyAssignedRoles = $request->getQuery('newlyAssignedRoles');
        $viewModel->setHasNewAedmRole($newlyAssignedRoles == 'AEDM');

        $result->setTemplate('2fa/register-card/success');
        $result->layout()->setBreadcrumbs($this->viewStrategy->breadcrumbs());
        $result->setViewModel($viewModel);
        return $result;
    }
}
