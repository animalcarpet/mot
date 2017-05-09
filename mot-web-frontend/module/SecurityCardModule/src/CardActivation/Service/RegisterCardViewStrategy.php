<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service;

use Core\Routing\ProfileRoutes;
use Core\Service\MotFrontendAuthorisationServiceInterface;
use Core\Service\MotFrontendIdentityProvider;
use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaFeature\FeatureToggles;
use Zend\View\Helper\Url;

class RegisterCardViewStrategy implements AutoWireableInterface
{
    /** @var FeatureToggles */
    private $featureToggles;

    /** @var RegisterCardHardStopCondition */
    private $hardStopCondition;

    /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
    private $authorisationService;

    /** @var MotFrontendIdentityProvider $identityProvider */
    private $identityProvider;

    /** @var SecurityCardGuard $securityCardGuard */
    private $securityCardGuard;

    /** @var PersonProfileGuardBuilder $personProfileGuardBuilder */
    private $personProfileGuardBuilder;

    /** @var Url $url */
    private $url;

    /**
     * RegisterCardViewStrategy constructor.
     *
     * @param FeatureToggles                       $featureToggles
     * @param RegisterCardHardStopCondition        $hardStopCondition
     * @param MotAuthorisationServiceInterface     $authorisationService
     * @param MotFrontendIdentityProviderInterface $identityProvider
     * @param SecurityCardGuard                    $securityCardGuard
     * @param PersonProfileGuardBuilder            $personProfileGuardBuilder
     */
    public function __construct(
        FeatureToggles $featureToggles,
        RegisterCardHardStopCondition $hardStopCondition,
        MotAuthorisationServiceInterface $authorisationService,
        MotFrontendIdentityProviderInterface $identityProvider,
        SecurityCardGuard $securityCardGuard,
        PersonProfileGuardBuilder $personProfileGuardBuilder,
        Url $url
    ) {
        $this->featureToggles = $featureToggles;
        $this->hardStopCondition = $hardStopCondition;
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
        $this->securityCardGuard = $securityCardGuard;
        $this->personProfileGuardBuilder = $personProfileGuardBuilder;
        $this->url = $url;
    }

    /**
     * If user cannot activate a card, it will not put them
     * On to the activate a card journey.
     *
     * @return bool
     */
    public function canActivateACard()
    {
        $identity = $this->identityProvider->getIdentity();
        $hasActiveCard = $this->securityCardGuard->hasActiveTwoFaCard($identity);
        $hasCardOrdered = $this->securityCardGuard->hasOutstandingCardOrdersAndNoActiveCard($identity);
        $twoFactorEligibleUser = $this->securityCardGuard->is2faEligibleUserWhichCanActivateACard($identity);

        return $twoFactorEligibleUser && (!$hasActiveCard || $hasCardOrdered);
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
            $breadcrumbs[] = ['Your profile' => ProfileRoutes::of($this->url)->yourProfile()];
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
