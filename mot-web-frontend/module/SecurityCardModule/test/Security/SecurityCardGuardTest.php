<?php

namespace Dvsa\Mot\Frontend\SecurityCardTest\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dvsa\Mot\ApiClient\Resource\Collection;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCard;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardOrder;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\MotFrontendIdentityInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\Security\SecurityCardGuard;
use Dvsa\Mot\Frontend\SecurityCardModule\Service\SecurityCardService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaCommon\Model\TesterGroupAuthorisationStatus;
use DvsaCommonTest\TestUtils\XMock;
use DvsaFeature\FeatureToggles;
use PHPUnit_Framework_TestCase;
use stdClass;
use UserAdmin\Service\PersonRoleManagementService;
use Core\Authorisation;

class SecurityCardGuardTest extends PHPUnit_Framework_TestCase
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
     * @var PersonRoleManagementService
     */
    private $personRoleManagementService;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    /**
     * @var MotFrontendIdentityInterface
     */
    private $identity;

    /**
     * @var TesterAuthorisation
     */
    private $testerAuthorisation;

    /**
     * @var MotFrontendAuthorisationServiceInterface $authorisationService
     */
    private $authorisationService;

    public function setUp()
    {
        $this->securityCardService = XMock::of(SecurityCardService::class);
        $this->authorisationServiceClient = XMock::of(AuthorisationService::class);
        $this->personRoleManagementService = XMock::of(PersonRoleManagementService::class);
        $this->identity = XMock::of(MotFrontendIdentityInterface::class);
        $this->authorisationService = XMock::of(MotFrontendAuthorisationServiceInterface::class);

        $this->withFeatureToggleEnabled(true);
    }

    public function testHasActiveCard_isFalse_whenFeatureToggleIsDisabled()
    {
        $this->withFeatureToggleEnabled(false);

        $this->assertFalse($this->buildSecurityCardGuard()->hasActiveTwoFaCard($this->identity));
    }

    public function testHasActiveCard_isFalse_whenNoCardExists()
    {
        $this->securityCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->willReturn(null);

        $this->assertFalse($this->buildSecurityCardGuard()->hasActiveTwoFaCard($this->identity));
    }

    public function testHasActiveCard_isFalse_whenOnlyInactiveCardExists()
    {
        $inactiveSecurityCard = new SecurityCard((object) ['active' => false]);

        $this->securityCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->willReturn($inactiveSecurityCard);

        $this->assertFalse($this->buildSecurityCardGuard()->hasActiveTwoFaCard($this->identity));
    }

    public function testHasActiveCard_isTrue_whenActiveCardExists()
    {
        $activeSecurityCard = new SecurityCard((object) ['active' => true]);

        $this->securityCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->willReturn($activeSecurityCard);

        $this->assertTrue($this->buildSecurityCardGuard()->hasActiveTwoFaCard($this->identity));
    }

    public function testEligibleForReplacementCard_isFalse_whenFeatureToggleIsDisabled()
    {
        $this->withFeatureToggleEnabled(false);

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForReplacementTwoFaCard($this->identity));
    }

    public function testEligibleForReplacementCard_isFalse_whenNotLoggedInAsLostOrForgotten()
    {
        $this->withIsAuthenticatedWithLostOrForgotten(false);

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForReplacementTwoFaCard($this->identity));
    }

    public function testEligibleForReplacementCard_isFalse_whenNoActiveCard()
    {
        $this
            ->withIsAuthenticatedWithLostOrForgotten(true)
            ->withInactiveSecurityCard();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForReplacementTwoFaCard($this->identity));
    }

    public function testEligibleForReplacementCard_isTrue_whenLoggedInAsLostOrForgottenAndHasActiveCard()
    {
        $this
            ->withIsAuthenticatedWithLostOrForgotten(true)
            ->withActiveSecurityCard();

        $guard = $this->buildSecurityCardGuard();

        $this->assertTrue($guard->isEligibleForReplacementTwoFaCard($this->identity));
    }

    public function testEligibleForNewCard_isFalse_whenFeatureToggleIsDisabled()
    {
        $this->withFeatureToggleEnabled(false);

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterMtessSubmission($this->identity, new TesterAuthorisation()));
    }

    public function testEligibleForNewCard_isFalse_whenNotInDemoTestNeeded()
    {
        $this->withNoDemoTestNeeded();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterMtessSubmission($this->identity, $this->testerAuthorisation));
    }

    public function testEligibleForNewCard_isFalse_whenInDemoTestNeededAndAlreadyHasCardOrder()
    {
        $this
            ->withDemoTestNeeded()
            ->withSecurityCardOrder();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterMtessSubmission($this->identity, $this->testerAuthorisation));
    }

    public function testEligibleForNewCard_isTrue_whenInDemoTestNeededAndNoCardOrders()
    {
        $this
            ->withDemoTestNeeded()
            ->withNoSecurityCardOrder();

        $guard = $this->buildSecurityCardGuard();

        $this->assertTrue($guard->isEligibleForNewTwoFaCardAfterMtessSubmission($this->identity, $this->testerAuthorisation));
    }

    public function testEligibleAfterNomination_isFalse_whenFeatureToggleIsDisabled()
    {
        $this->withFeatureToggleEnabled(false);

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterNomination($this->identity));
    }

    public function testEligibleAfterNomination_isFalse_whenNoPendingRoles()
    {
        $this
            ->withPendingRoleNomination(false)
            ->withNoSecurityCardOrder();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterNomination($this->identity));
    }

    public function testEligibleAfterNomination_isFalse_whenPendingRoleButAlreadyHasCardOrder()
    {
        $this
            ->withPendingRoleNomination(true)
            ->withSecurityCardOrder();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterNomination($this->identity));
    }

    public function testEligibleAfterNomination_isFalse_whenPendingRoleAndNoCardOrderButHasActiveCard()
    {
        $this
            ->withPendingRoleNomination(true)
            ->withNoSecurityCardOrder()
            ->withActiveSecurityCard();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->isEligibleForNewTwoFaCardAfterNomination($this->identity));
    }

    public function testEligibleAfterNomination_isTrue_whenPendingRoleAndNoCardOrderAndNoActiveCard()
    {
        $this
            ->withPendingRoleNomination(true)
            ->withNoSecurityCardOrder()
            ->withNoSecurityCard();

        $guard = $this->buildSecurityCardGuard();

        $this->assertTrue($guard->isEligibleForNewTwoFaCardAfterNomination($this->identity));
    }

    public function testOutstandingOrdersAndNoActiveCard_isFalse_whenFeatureToggleIsDisabled()
    {
        $this->withFeatureToggleEnabled(false);

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->hasOutstandingCardOrdersAndNoActiveCard($this->identity));
    }

    public function testOutstandingOrdersAndNoActiveCard_isFalse_whenNoOutstandingOrders()
    {
        $this->withNoSecurityCardOrder();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->hasOutstandingCardOrdersAndNoActiveCard($this->identity));
    }

    public function testOutstandingOrdersAndNoActiveCard_isFalse_whenOutstandingOrdersButActiveCard()
    {
        $this
            ->withSecurityCardOrder()
            ->withActiveSecurityCard();

        $guard = $this->buildSecurityCardGuard();

        $this->assertFalse($guard->hasOutstandingCardOrdersAndNoActiveCard($this->identity));
    }

    public function testOutstandingOrdersAndNoActiveCard_isTrue_whenOutstandingOrdersAndNoActiveCard()
    {
        $this
            ->withSecurityCardOrder()
            ->withInactiveSecurityCard();

        $guard = $this->buildSecurityCardGuard();

        $this->assertTrue($guard->hasOutstandingCardOrdersAndNoActiveCard($this->identity));
    }

    public function testHasPermissionToOrderForOtherUserHasPermissionShouldReturnTrue()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::CAN_ORDER_2FA_SECURITY_CARD_FOR_OTHER_USER)
            ->willReturn(true);

        $this->assertTrue($this->buildSecurityCardGuard()->hasPermissionToOrderCardForOtherUser());
    }

    public function testHasPermissionToOrderForOtherUserHasNoPermissionShouldReturnFalse()
    {
        $this->authorisationService
            ->expects($this->once())
            ->method('isGranted')
            ->with(PermissionInSystem::CAN_ORDER_2FA_SECURITY_CARD_FOR_OTHER_USER)
            ->willReturn(false);

        $this->assertFalse($this->buildSecurityCardGuard()->hasPermissionToOrderCardForOtherUser());
    }

    public function testHasInactiveCardReturnsTrueWhenUserHasInactiveCard()
    {
        $this->withInactiveSecurityCard();
        $this->withFeatureToggleEnabled(true);

        $this->assertTrue($this->buildSecurityCardGuard()->hasInactiveTwoFaCard($this->identity));
    }

    public function testHasInactiveCardReturnsFalseWhenUserHasAnActiveCard()
    {
        $this->withActiveSecurityCard();
        $this->withFeatureToggleEnabled(true);

        $this->assertFalse($this->buildSecurityCardGuard()->hasInactiveTwoFaCard($this->identity));
    }

    public function testHasInactiveCardReturnsFalseWhenTwoFaOff()
    {
        $this->withFeatureToggleEnabled(false);

        $this->assertFalse($this->buildSecurityCardGuard()->hasInactiveTwoFaCard($this->identity));
    }

    private function withActiveSecurityCard()
    {
        $activeSecurityCard = new SecurityCard((object) ['active' => true]);

        return $this->withSecurityCard($activeSecurityCard);
    }

    private function withInactiveSecurityCard()
    {
        $inactiveSecurityCard = new SecurityCard((object) ['active' => false]);

        return $this->withSecurityCard($inactiveSecurityCard);
    }

    private function withSecurityCard(SecurityCard $securityCard)
    {
        $this->securityCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->willReturn($securityCard);

        return $this;
    }

    private function withNoSecurityCard()
    {
        $this->securityCardService
            ->expects($this->any())
            ->method('getSecurityCardForUser')
            ->willReturn(null);

        return $this;
    }

    private function withIsAuthenticatedWithLostOrForgotten($isLostOrForgotten)
    {
        $this->identity
            ->expects($this->any())
            ->method('isAuthenticatedWithLostForgotten')
            ->willReturn($isLostOrForgotten);

        return $this;
    }

    private function withDemoTestNeeded()
    {
        $demoTestNeededStatus = new TesterGroupAuthorisationStatus(
            AuthorisationForTestingMotStatusCode::DEMO_TEST_NEEDED,
            'Demo test needed'
        );

        $this->testerAuthorisation = new TesterAuthorisation($demoTestNeededStatus, $demoTestNeededStatus);

        return $this;
    }

    private function withNoDemoTestNeeded()
    {
        $this->testerAuthorisation = new TesterAuthorisation();

        return $this;
    }

    private function withSecurityCardOrder()
    {
        $securityCardOrderData = [
            "submittedOn" => "2014-05-25",
            "fullName" => "AUTH_INT_YOTURWDKSPFUMKBPMGMU AUTH_INT_YOTURWDKSPFUMKBPMGMU",
            "recipientName" => "",
            "addressLine1" => "9f1341",
            "addressLine2" => "5 Uncanny St",
            "addressLine3" => "fake address line 3",
            "postcode" => "L1 1PQ",
            "town" => "Liverpool"
        ];

        $this->authorisationServiceClient
            ->expects($this->any())
            ->method('getSecurityCardOrders')
            ->willReturn(new Collection([new stdClass($securityCardOrderData)], SecurityCardOrder::class));

        return $this;
    }

    private function withNoSecurityCardOrder()
    {
        $this->authorisationServiceClient
            ->expects($this->any())
            ->method('getSecurityCardOrders')
            ->willReturn(new Collection([], SecurityCardOrder::class));

        return $this;
    }

    private function withPendingRoleNomination($isPending)
    {
        $this->personRoleManagementService
            ->expects($this->any())
            ->method('personHasPendingRole')
            ->willReturn($isPending);

        return $this;
    }

    private function withFeatureToggleEnabled($isFeatureToggleEnabled)
    {
        $this->twoFaFeatureToggle = new TwoFaFeatureToggle(
            new FeatureToggles([FeatureToggle::TWO_FA => $isFeatureToggleEnabled])
        );

        return $this;
    }

    private function buildSecurityCardGuard()
    {
        return new SecurityCardGuard(
            $this->securityCardService,
            $this->authorisationServiceClient,
            $this->personRoleManagementService,
            $this->twoFaFeatureToggle,
            $this->authorisationService);
    }
}
