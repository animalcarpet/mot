<?php

namespace ApplicationTest\View\Helper;

use Application\View\Helper\CanTestWithoutOtp;
use Core\Service\MotFrontendIdentityProvider;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonTest\TestUtils\XMock;
use PHPUnit_Framework_TestCase;
use Application\Service\CanTestWithoutOtpService;

class CanTestWithoutOtpTest extends PHPUnit_Framework_TestCase
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
            ->createHelper();

        $this->assertTrue($canTestWithoutOtp());
    }

    public function testCannotTestWithoutOtpIfToggleEnabledAndUserDoesNotHaveTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(true)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createHelper();

        $this->assertFalse($canTestWithoutOtp());
    }

    public function testCannotTestWithoutOtpIfToggleNotEnabledAndUserHasTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(true)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createHelper();

        $this->assertFalse($canTestWithoutOtp());
    }

    public function testCannotTestWithoutOtpIfToggleNotEnabledAndUserDoesNotHaveTwoFactorAuth()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(false)
            ->createHelper();

        $this->assertFalse($canTestWithoutOtp());
    }

    public function testCanAlwaysTestWithoutOtpIfPermissionGranted()
    {
        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(true)
            ->createHelper();

        $this->assertTrue($canTestWithoutOtp());

        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(true)
            ->withUserSignedUpToTwoFactorAuth(false)
            ->withTestWithoutOtpPermissionGranted(true)
            ->createHelper();

        $this->assertTrue($canTestWithoutOtp());

        $canTestWithoutOtp = $this
            ->withTwoFactorAuthToggleEnabled(false)
            ->withUserSignedUpToTwoFactorAuth(true)
            ->withTestWithoutOtpPermissionGranted(true)
            ->createHelper();

        $this->assertTrue($canTestWithoutOtp());
    }

    /**
     * @param bool $signedUp
     *
     * @return CanTestWithoutOtpTest
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
     * @return CanTestWithoutOtpTest
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
     * @return CanTestWithoutOtpTest
     */
    private function withTwoFactorAuthToggleEnabled($toggleEnabled)
    {
        $this->featureTogglesMock->expects($this->any())
            ->method('isEnabled')
            ->willReturn($toggleEnabled);

        return $this;
    }

    /**
     * @return CanTestWithoutOtp
     */
    private function createHelper()
    {
        $this->canTestWithoutOtpService = new CanTestWithoutOtpService(
            $this->identityProvider,
            $this->authorisationServiceMock,
            $this->featureTogglesMock
        );

        return new CanTestWithoutOtp($this->canTestWithoutOtpService);
    }
}
