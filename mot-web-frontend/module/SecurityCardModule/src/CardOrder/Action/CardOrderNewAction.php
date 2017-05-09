<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderNewViewModel;
use Zend\Http\Request;

class CardOrderNewAction
{
    /**
     * @var OrderNewSecurityCardSessionService
     */
    private $sessionService;

    /**
     * @var OrderSecurityCardStepService
     */
    private $stepService;

    /**
     * @var MotFrontendIdentityProvider
     */
    private $identityProvider;

    /**
     * @var CardOrderProtection
     */
    private $cardOrderProtection;

    public function __construct(OrderNewSecurityCardSessionService $sessionService,
                                OrderSecurityCardStepService $stepService,
                                MotFrontendIdentityProvider $identityProvider,
                                CardOrderProtection $cardOrderProtection)
    {
        $this->sessionService = $sessionService;
        $this->stepService = $stepService;
        $this->identityProvider = $identityProvider;
        $this->cardOrderProtection = $cardOrderProtection;
    }

    public function execute(Request $request, $userId)
    {
        $cardOrderProtectionResult = $this->cardOrderProtection->checkAuthorisation($userId);

        if ($cardOrderProtectionResult instanceof RedirectToRoute) {
            return $cardOrderProtectionResult;
        }

        $result = new ViewActionResult();

        $identity = $this->identityProvider->getIdentity();
        $hasActiveCard = $identity->isSecondFactorRequired();

        $viewModel = new CardOrderNewViewModel();
        $viewModel
            ->setHasAnActiveCard($hasActiveCard)
            ->setUserId($userId);

        $result->setViewModel($viewModel);
        $result->setTemplate('2fa/card-order/start');

        $this->setUpSession($userId);
        $this->stepService->updateStepStatus($userId, OrderSecurityCardStepService::ADDRESS_STEP, true);

        return $result;
    }

    private function setUpSession($userId)
    {
        $sessionArray = [
            'userId' => $userId,
            OrderNewSecurityCardSessionService::HAS_ORDERED_STORE => false,
        ];

        $stepArray = [];

        foreach ($this->stepService->getSteps() as $step) {
            $stepArray[$step] = false;
        }

        $stepArray[OrderSecurityCardStepService::NEW_STEP] = true;

        $sessionArray[OrderNewSecurityCardSessionService::STEP_SESSION_STORE] = $stepArray;

        $this->sessionService->saveToGuid($userId, $sessionArray);
    }
}
