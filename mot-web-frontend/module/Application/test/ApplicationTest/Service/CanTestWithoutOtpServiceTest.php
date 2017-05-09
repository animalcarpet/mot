<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace ApplicationTest\Service;

use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use Application\Service\CanTestWithoutOtpService;

class CanTestWithoutOtpServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var MotFrontendIdentityProvider */
    private $identityProvider;

    /** @var MotAuthorisationServiceInterface */
    private $authorisationServiceMock;

    /** @var TwoFaFeatureToggle */
    private $featureTogglesMock;

    /** @var CanTestWithoutOtpService $canTestWithoutOtpService */
    private $canTestWithoutOtpService;

    public function setUp()
    {
        $this->identityProvider = XMock::of(MotFrontendIdentityProvider::class);
        $this->authorisationServiceMock = XMock::of(MotAuthorisationServiceInterface::class);
        $this->featureTogglesMock = XMock::of(TwoFaFeatureToggle::class);
    }

    public function testCanTestWithoutOtpIfToggleEnabledAndUserHasTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(true)
            ->withUserSignedUpToTwoFactorAuth(true)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createService();

        $this->assertTrue($canTestWithoutOtp->canTestWithoutOtp());
    }

    public function testCannotTestWithoutOtpIfToggleEnabledAndUserDoesNotHaveTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(true)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createService();

        $this->assertFalse($canTestWithoutOtp->canTestWithoutOtp());
    }

    public function testCannotTestWithoutOtpIfToggleNotEnabledAndUserHasTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(true)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createService();

        $this->assertFalse($canTestWithoutOtp->canTestWithoutOtp());
    }

    public function testCannotTestWithoutOtpIfToggleNotEnabledAndUserDoesNotHaveTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createService();

        $this->assertFalse($canTestWithoutOtp->canTestWithoutOtp());
    }

    public function testCanAlwaysTestWithoutOtpIfPermissionGranted()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(true)
            ->createService();

        $this->assertTrue($canTestWithoutOtp->canTestWithoutOtp());

        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(true)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(true)
            ->createService();

        $this->assertTrue($canTestWithoutOtp->canTestWithoutOtp());

        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(true)
            ->withTestWithoutOtpPermissionGranted(true)
            ->createService();

        $this->assertTrue($canTestWithoutOtp->canTestWithoutOtp());
    }

    /**
     * @param bool $signedUp
     *
     * @return CanTestWithoutOtpServiceTest
     */
    private function withUserSignedUpToTwoFactorAuth($signedUp)
    {
        $identity = new Identity();
        $identity->setSecondFactorRequired($signedUp);

        $this->identityProvider->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identity);

        return $this;
    }

    /**
     * @param bool $testWithoutOtp
     *
     * @return CanTestWithoutOtpServiceTest
     */
    private function withTestWithoutOtpPermissionGranted($testWithoutOtp)
    {
        $this->authorisationServiceMock->expects($this->any())
            ->method('isGranted')
            ->with($this->equalTo(PermissionInSystem::MOT_TEST_WITHOUT_OTP))
            ->willReturn($testWithoutOtp);

        return $this;
    }

    /**
     * @param bool $toggleEnabled
     *
     * @return CanTestWithoutOtpServiceTest
     */
    private function withTwoFactorAuthToggleEnabled($toggleEnabled)
    {
        $this->featureTogglesMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($toggleEnabled);

        return $this;
    }

    /**
     * @return CanTestWithoutOtpService
     */
    private function createService()
    {
        return $this->canTestWithoutOtpService = new CanTestWithoutOtpService(
            $this->identityProvider,
            $this->authorisationServiceMock,
            $this->featureTogglesMock
        );
    }
}
