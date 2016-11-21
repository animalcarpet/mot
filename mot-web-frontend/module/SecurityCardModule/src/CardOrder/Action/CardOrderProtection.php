<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action;

use Core\Action\ViewActionResult;
use Core\Action\RedirectToRoute;
use Core\Service\MotFrontendIdentityProvider;
use Dashboard\Controller\UserHomeController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Controller\AlreadyOrderedNewCardController;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderNewViewModel;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;

class CardOrderProtection implements AutoWireableInterface
{
    /**
     * @var MotFrontendIdentityProvider $identityProvider
     */
    private $identityProvider;

    /**
     * @var SecurityCardGuard $securityCardGuard
     */
    private $securityCardGuard;

    /**
     * @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper
     */
    private $testerGroupAuthorisationMapper;

    /**
     * @var TwoFaFeatureToggle $twoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    public function __construct(MotFrontendIdentityProvider $identityProvider,
                                SecurityCardGuard $securityCardGuard,
                                TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
                                TwoFaFeatureToggle $twoFaFeatureToggle)
    {
        $this->identityProvider = $identityProvider;
        $this->securityCardGuard = $securityCardGuard;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }

    public function checkAuthorisation($userId)
    {
        $featureToggleNotEnabled = !$this->twoFaFeatureToggle->isEnabled();

        if ($featureToggleNotEnabled) {
            return new RedirectToRoute(UserHomeController::ROUTE);
        }

        $identity = $this->identityProvider->getIdentity();

        if (intval($userId) != $identity->getUserId() && !$this->securityCardGuard->hasPermissionToOrderCardForOtherUser()) {
            return new RedirectToRoute(UserHomeController::ROUTE);
        }

        $cannotEnterJourney = !$this->canEnterJourney();
        $hasOutstandingCardOrder = $this->securityCardGuard->hasOutstandingCardOrdersAndNoActiveCard($identity);

        if ($cannotEnterJourney && $hasOutstandingCardOrder) {
            return new RedirectToRoute(AlreadyOrderedNewCardController::ROUTE);
        } else if ($cannotEnterJourney) {
            return new RedirectToRoute(UserHomeController::ROUTE);
        }
    }

    private function canEnterJourney()
    {
        $identity = $this->identityProvider->getIdentity();
        $testerAuthorisation = $this->testerGroupAuthorisationMapper->getAuthorisation($identity->getUserId());

        return $this->securityCardGuard->canOrderSecurityCard($identity, $testerAuthorisation);
    }
}