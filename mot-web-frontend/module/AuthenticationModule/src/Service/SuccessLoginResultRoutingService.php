<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Core\Action\RedirectToRoute;
use Core\Action\RedirectToUrl;
use Core\Service\LazyMotFrontendAuthorisationService;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\WebLoginResult;
use Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Controller\RegisterCardInformationController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Controller\RegisteredCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\NewUserOrderCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Controller\RegisterCardInformationNewUserController;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\Request;
use Zend\Authentication\AuthenticationService;

class SuccessLoginResultRoutingService implements AutoWireableInterface
{
    /**  * @var AuthorisationService */
    private $authorisationServiceClient;

    /** @var AuthenticationService */
    private $authenticationService;

    /** @var LazyMotFrontendAuthorisationService */
    private $authorisationService;

    /** @var GotoUrlService */
    private $gotoUrlService;

    /** @var TwoFaFeatureToggle */
    private $twoFaFeatureToggle;

    public function __construct(
        AuthorisationService $authorisationServiceClient,
        AuthenticationService $authenticationService,
        LazyMotFrontendAuthorisationService $authorisationService,
        GotoUrlService $gotoUrlService,
        TwoFaFeatureToggle $twoFaFeatureToggle
    ) {
        $this->authorisationServiceClient = $authorisationServiceClient;
        $this->authenticationService = $authenticationService;
        $this->authorisationService = $authorisationService;
        $this->gotoUrlService = $gotoUrlService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    public function route(WebLoginResult $loginResult, Request $request)
    {
        if ($this->userMaySeeA2FAPageAfterLogin()) {
            $token = $loginResult->getToken();
            $userId = $this->authenticationService->getIdentity()->getUserId();

            if ($this->authenticationService->getIdentity()->isSecondFactorRequired()) {
                if ($this->userOrderedReplacementCard($token)) {
                    return new RedirectToRoute(LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE);
                }

                return new RedirectToRoute(RegisteredCardController::ROUTE);
            }
            if ($this->authorisationService->isTradeUser()) {
                return new RedirectToRoute(
                    RegisterCardInformationController::REGISTER_CARD_INFORMATION_ROUTE, ['userId' => $userId]
                );
            }
            if ($this->userHasAlreadyOrderedACard($token)) {
                return new RedirectToRoute(
                    RegisterCardInformationNewUserController::REGISTER_CARD_NEW_USER_INFORMATION_ROUTE,
                    ['userId' => $userId]
                );
            }

            return new RedirectToRoute(NewUserOrderCardController::ORDER_CARD_NEW_USER_ROUTE, ['userId' => $userId]);
        }

        $rawGoto = $request->getPost('goto');
        $goto = $this->gotoUrlService->decodeGoto($rawGoto);
        if ($goto) {
            return new RedirectToUrl($goto);
        } else {
            return new RedirectToRoute(UserHomeController::ROUTE);
        }
    }

    /**
     * Determine if the user has ever ordered a card.
     *
     * @return bool
     */
    private function userHasAlreadyOrderedACard($token)
    {
        $identity = $this->authenticationService->getIdentity();

        /** @var Collection $orders */
        $orders = $this->authorisationServiceClient->getSecurityCardOrders($identity->getUsername(), null, null,
            $token);

        return $orders->getCount() > 0;
    }

    /**
     * @return bool
     */
    private function userOrderedReplacementCard($token)
    {
        $identity = $this->authenticationService->getIdentity();

        try {
            $securityCard = $this->authorisationServiceClient->getSecurityCardForUser($identity->getUsername(), $token);
        } catch (ResourceNotFoundException $exception) {
            return false;
        }

        $hasOrderedACard = $this->userHasAlreadyOrderedACard($token);

        return $hasOrderedACard && !$securityCard->isActive();
    }

    /**
     * Determine if the user should see an activation
     * or order card screen after login
     * (2FA toggle is on) AND (NOT DVSA User) AND has2FAPermission AND (NOT alreadyRegisteredFor2Fa).
     *
     * @return bool
     */
    private function userMaySeeA2FAPageAfterLogin()
    {
        return $this->twoFaFeatureToggle->isEnabled()
        && !$this->authorisationService->isDvsa()
        && ($this->authorisationService->isGranted(PermissionInSystem::AUTHENTICATE_WITH_2FA)
            || $this->authorisationService->isNewTester());
    }
}
