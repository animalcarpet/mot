package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.interfaces.WarningPage;
import uk.gov.dvsa.ui.pages.login.LockOutWarningPage;
import uk.gov.dvsa.ui.pages.login.LockedAccountWarningPage;
import uk.gov.dvsa.ui.pages.login.LoginPage;

import java.io.IOException;

public class OpenAMLockoutWarningTests extends DslTest {

    private int warningAttempts = 4;
    private int lockoutAttempts = 5;

    @Test(groups = {"Regression", "VM-12163"},
            description = "Test that validates the that user is redirected to authentication failed page after 4 incorrect password attempts")
    public void authenticationFailedFor4Times() throws IOException, InterruptedException {

        // Given I have entered incorrect login details 3 times
        final User user = motApi.user.createTester(siteData.createSite().getId());
        motApi.createMultipleSession(user.getUsername(), "Wrong", warningAttempts);

        // When I try for the 4th time
        LoginPage loginPage = pageNavigator.goToLoginPage();
        WarningPage warningPage = loginPage.login(user.getUsername(), "Wrong", LockOutWarningPage.class);

        // Then I am redirected to Lockout warning page
        warningPage.isMessageDisplayed();
    }

    @Test(groups = {"Regression", "VM-12163"})
    public void lockAccountAfterInvalidAttempts() throws IOException, InterruptedException {

        // Given I have entered incorrect login details 4 times
        final User user = motApi.user.createTester(siteData.createSite().getId());
        motApi.createMultipleSession(user.getUsername(), "Wrong", lockoutAttempts);

        // When I try the 5th time
        LoginPage loginPage = pageNavigator.goToLoginPage();
        WarningPage warningPage = loginPage.login(user.getUsername(), "Wrong", LockedAccountWarningPage.class);

        // Then my account should be locked
        warningPage.isMessageDisplayed();
    }
}