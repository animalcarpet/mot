<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProvider;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;

class RegisterCardViewStrategy implements AutoWireableInterface
{
    /** @var FeatureToggles */
    private $featureToggles;

    /** @var  RegisterCardHardStopCondition */
    private $hardStopCondition;

    /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var  MotFrontendIdentityProvider $identityProvider */
    private $identityProvider;

    public function __construct(
        FeatureToggles $featureToggles,
        RegisterCardHardStopCondition $hardStopCondition,
        MotAuthorisationServiceInterface $authorisationService,
        MotFrontendIdentityProviderInterface $identityProvider
    ) {
        $this->featureToggles = $featureToggles;
        $this->hardStopCondition = $hardStopCondition;
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
    }

    public function canSee()
    {
        $twoFaEnabled = $this->featureToggles->isEnabled(FeatureToggle::TWO_FA);
        $isSecondFactorRequired = $this->identityProvider->getIdentity()->isSecondFactorRequired();
        $isAuthorisedTo2FA =
            $this->authorisationService->isGranted(PermissionInSystem::AUTHENTICATE_WITH_2FA) ||
            $this->authorisationService->isNewTester();

        return !$isSecondFactorRequired && $isAuthorisedTo2FA && $twoFaEnabled;
    }

    public function pageSubTitle()
    {
        if ($this->hardStopCondition->isTrue()) {
            return '';
        } else {
            return 'Your profile';
        }
    }

    public function breadcrumbs()
    {
        $breadcrumbs = [];
        if (!$this->hardStopCondition->isTrue()) {
            $breadcrumbs[] = ['Your profile' => ContextProvider::YOUR_PROFILE_CONTEXT];
        }
        $breadcrumbs[] = ['Activate your security card' => ''];

        return $breadcrumbs;
    }

    public function skipCtaTemplate()
    {
        if ($this->hardStopCondition->isTrue()) {
            return '2fa/register-card/skip-cta/goToSignIn';
        } else {
            return '2fa/register-card/skip-cta/goToProfile';
        }
    }
}