<?php


namespace Application\Service;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaFeature\FeatureToggles;

use Zend\View\Helper\AbstractHelper;

class CanTestWithoutOtpService
{
    /**
     * @var Identity
     */
    private $identity;

    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    private $identityProvider;

    /**
     * @param MotFrontendIdentityProvider $identityProvider
     * @param MotAuthorisationServiceInterface $authorisationService
     * @param TwoFaFeatureToggle $twoFaFeatureToggle
     */
    public function __construct(MotFrontendIdentityProvider $identityProvider, MotAuthorisationServiceInterface $authorisationService,
                                TwoFaFeatureToggle $twoFaFeatureToggle)
    {
        $this->identityProvider = $identityProvider;
        $this->authorisationService = $authorisationService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
    }


    /**
     * @return bool
     */
    public function canTestWithoutOtp()
    {
        $this->identity = $this->identityProvider->getIdentity();

        if ($this->authorisationService->isGranted(PermissionInSystem::MOT_TEST_WITHOUT_OTP)) {
            return true;
        }

        return $this->identity->isSecondFactorRequired() && $this->twoFaFeatureToggle->isEnabled();
    }
}