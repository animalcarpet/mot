<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service;

use Core\Service\LazyMotFrontendAuthorisationService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\FeatureToggle;
use DvsaFeature\FeatureToggles;

class RegisterCardHardStopCondition
{
    /**
     * @var LazyMotFrontendAuthorisationService
     */
    private $authorisationService;

    /**
     * @var FeatureToggles
     */
    private $featureToggles;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    public function __construct(
        FeatureToggles $featureToggles,
        MotAuthorisationServiceInterface $authorisationService,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->authorisationService = $authorisationService;
        $this->featureToggles = $featureToggles;
        $this->identityProvider = $identityProvider;
    }

    public function isTrue()
    {
        $is2FaEnabled = $this->featureToggles->isEnabled(FeatureToggle::TWO_FA);
        $is2FaHardStop = $this->featureToggles->isEnabled(FeatureToggle::TWO_FA_HARD_STOP);
        $isTradeUser = $this->authorisationService->isTradeUser();
        $isDvsaUser = $this->authorisationService->isDvsa();
        $isNot2FaActive = !$this->identityProvider->getIdentity()->isSecondFactorRequired();

        return $is2FaEnabled && $is2FaHardStop && $isTradeUser && $isNot2FaActive && !$isDvsaUser;
    }
}
