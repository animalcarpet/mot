package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.testng.Assert;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.interfaces.WarningPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.TwoFactorLockOutWarningPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.TwoFactorLockedAccountWarningPage;
import uk.gov.dvsa.ui.pages.authentication.twofactorauth.TwoFactorPinEntryPage;

import java.io.IOException;

public class SecurityCardLockoutTests extends DslTest {

    private final int WARNING_ATTEMPTS = 9;
    private final int LOCKOUT_ATTEMPTS = 10;

    @Test(groups = {"Regression", "2fa"})
    public void twoFactorAuthenticationWarningDisplayedAfterNumberOfInvalidAttempts() throws IOException {

        // Given I am a 2FA active trade user who has entered my 6-digit 2FA pin incorrectly 3 times
        final User user = motApi.user.createTester(siteData.createSite().getId());

        TwoFactorPinEntryPage twoFactorPinEntryPage = motUI.loginExpectingPinEntryPage(user);
        motUI.enterSecurityPinMultipleTimes("000000", WARNING_ATTEMPTS - 1);

        // When I enter my pin incorrectly a 4th time
        WarningPage warningPage = twoFactorPinEntryPage.enterTwoFactorPinAndSubmit("000000", TwoFactorLockOutWarningPage.class);

        // Then I am shown a warning message that one more incorrect attempt will lock my account
        Assert.assertTrue(warningPage.isMessageDisplayed());
    }

    @Test(groups = {"Regression", "2fa"})
    public void twoFactorAuthenticationLockAccountWarningDisplayedAfterNumberOfInvalidAttempts() throws IOException {

        // Given I am a 2FA active trade user
        final User user = motApi.user.createTester(siteData.createSite().getId());

        motUI.loginExpectingPinEntryPage(user);
        motUI.enterSecurityPinMultipleTimes("000000", LOCKOUT_ATTEMPTS - 1);

        TwoFactorPinEntryPage twoFactorPinEntryPage = pageNavigator.goToTwoFactorPinEntryPage();

        // When I enter my 6-digit 2FA pin incorrectly a certain number of times over a 5 minute time period
        WarningPage warningPage = twoFactorPinEntryPage.enterTwoFactorPinAndSubmit("000000", TwoFactorLockedAccountWarningPage.class);

        // Then I am displayed a validation message advising I cannot login
        Assert.assertTrue(warningPage.isMessageDisplayed());

        // And when I log out and log in again 
        motUI.logout(user);
        warningPage =  motUI.loginExpectingLockedAccountWarningPage(user);

        // Then my I am shown a message that my account is still locked
        Assert.assertTrue(warningPage.isMessageDisplayed());
    }
}