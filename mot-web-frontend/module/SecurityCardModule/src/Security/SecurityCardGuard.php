<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Security;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use UserAdmin\Service\PersonRoleManagementService;

class SecurityCardGuard
{
    /**
     * @var SecurityCardService
     */
    private $securityCardService;

    /**
     * @var AuthorisationService
     */
    private $authorisationServiceClient;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authorisationService;

    public function __construct(
        SecurityCardService $securityCardService,
        AuthorisationService $authorisationServiceClient,
        PersonRoleManagementService $personRoleManagementService,
        TwoFaFeatureToggle $twoFaFeatureToggle,
        MotFrontendAuthorisationServiceInterface $authorisationService
    ) {
        $this->securityCardService = $securityCardService;
        $this->authorisationServiceClient = $authorisationServiceClient;
        $this->personRoleManagementService = $personRoleManagementService;
        $this->twoFaFeatureToggle = $twoFaFeatureToggle;
        $this->authorisationService = $authorisationService;
    }

    public function hasActiveTwoFaCard(MotFrontendIdentityInterface $identity)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }

        $securityCard = $this->securityCardService->getSecurityCardForUser($identity->getUsername());

        return $securityCard instanceof SecurityCard && $securityCard->isActive();
    }

    public function hasInactiveTwoFaCard(MotFrontendIdentityInterface $identity)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }
        $securityCard = $this->securityCardService->getSecurityCardForUser($identity->getUsername());

        return $securityCard instanceof SecurityCard && !$securityCard->isActive();
    }

    public function isEligibleForReplacementTwoFaCard(MotFrontendIdentityInterface $identity)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }

        return $identity->isAuthenticatedWithLostForgotten() && $this->hasActiveTwoFaCard($identity);
    }

    public function isEligibleForNewTwoFaCardAfterMtessSubmission(MotFrontendIdentityInterface $identity, TesterAuthorisation $testerAuthorisation)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }

        $demoTestNeeded =
            $this->isDemoTestNeededStatus($testerAuthorisation->getGroupAStatus()) ||
            $this->isDemoTestNeededStatus($testerAuthorisation->getGroupBStatus());

        return $demoTestNeeded && $this->hasNoSecurityCardOrders($identity) && !$this->hasActiveTwoFaCard($identity);
    }

    public function isEligibleForActivatingTwoFaCardAfterNomination(MotFrontendIdentityInterface $identity)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }

        $hasPendingRoleNomination = $this->personRoleManagementService->personHasPendingRole($identity->getUserId());
        $hasSecurityCardOrders = $this->hasOutstandingCardOrdersAndNoActiveCard($identity);
        $hasNoActiveSecurityCard = !$this->hasActiveTwoFaCard($identity);

        return $hasPendingRoleNomination && $hasSecurityCardOrders && $hasNoActiveSecurityCard;
    }

    public function isEligibleForNewTwoFaCardAfterNomination(MotFrontendIdentityInterface $identity)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }

        $hasPendingRoleNomination = $this->personRoleManagementService->personHasPendingRole($identity->getUserId());
        $hasNoSecurityCardOrders = $this->hasNoSecurityCardOrders($identity);
        $hasNoActiveSecurityCard = !$this->hasActiveTwoFaCard($identity);

        return $hasPendingRoleNomination && $hasNoSecurityCardOrders && $hasNoActiveSecurityCard;
    }

    public function canOrderSecurityCard(MotFrontendIdentityInterface $identity, TesterAuthorisation $testerAuthorisation)
    {
        return
            $this->isEligibleForNewTwoFaCardAfterMtessSubmission($identity, $testerAuthorisation) ||
            $this->isEligibleForNewTwoFaCardAfterNomination($identity) ||
            $this->isEligibleForReplacementTwoFaCard($identity) ||
            $this->hasPermissionToOrderCardForOtherUser();
    }

    public function hasOutstandingCardOrdersAndNoActiveCard(MotFrontendIdentityInterface $identity)
    {
        if (!$this->twoFaFeatureToggle->isEnabled()) {
            return false;
        }

        return $this->hasSecurityCardOrders($identity) && !$this->hasActiveTwoFaCard($identity);
    }

    public function is2faEligibleUserWhichCanActivateACard(MotFrontendIdentityInterface $identity)
    {
         $hasSecurityCardOrders = $this->hasSecurityCardOrders($identity);
         $isEligibleForActivatingAfterNomination = $this->isEligibleForActivatingTwoFaCardAfterNomination($identity);
         $isEligibleForReplacementCard = $this->isEligibleForReplacementTwoFaCard($identity);
         $isGrantedToOrderCard = $this->authorisationService->isGranted(PermissionInSystem::CAN_ORDER_2FA_SECURITY_CARD);

        return $hasSecurityCardOrders || $isEligibleForActivatingAfterNomination ||
                $isEligibleForReplacementCard || $isGrantedToOrderCard;
    }

    private function isDemoTestNeededStatus($status)
    {
        if (!$status instanceof TesterGroupAuthorisationStatus) {
            return false;
        }

        return $status->getCode() == AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED;
    }

    public function hasSecurityCardOrders(MotFrontendIdentityInterface $identity)
    {
        $orders = $this->authorisationServiceClient->getSecurityCardOrders($identity->getUsername());

        return $orders->getCount() > 0;
    }

    private function hasNoSecurityCardOrders(MotFrontendIdentityInterface $identity)
    {
        return !$this->hasSecurityCardOrders($identity);
    }

    public function hasPermissionToOrderCardForOtherUser()
    {
        return $this->authorisationService->isGranted(PermissionInSystem::CAN_ORDER_2FA_SECURITY_CARD_FOR_OTHER_USER);
    }
}
