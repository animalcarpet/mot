package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.shared.role.RoleManager;
import uk.gov.dvsa.domain.shared.role.TradeRoles;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.login.LoginPage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;
import static org.hamcrest.Matchers.is;

import static uk.gov.dvsa.matcher.UrlMatcher.isPresentInCurrentUrl;

public class LoginTests extends DslTest {

    @Test(groups = {"BVT"})
    void dvsaUserCanLogInSuccessfullyViaLoginPage() throws IOException {
        step("Given I am a DVSA user");
        User validUser = motApi.user.createAreaOfficeOne("dvsa");

        //When I login through the login page
        motUI.login(validUser);

        //Then my login is successful
        assertThat(motUI.isLoginSuccessful(), is(true));
    }

    @Test(groups = {"2fa"})
    public void userCanActivateCard() throws IOException {
        //Given I am an authenticated user
        User twoFactorUser = motApi.user.createNon2FaTester(siteData.createSite().getId());

        //When I submit a 2fa card activation request with correct details
        String activationMessage = motUI.authentication.securityCard.activate2faCard(twoFactorUser).getConfirmationText();

        //Then my activation is successful
        assertThat("Activation Successful", activationMessage, containsString("Your security card has been activated"));
    }

    @Test(testName = "2faHardStopDisabled", groups = {"2fa"})
    public void userCanSkipActivationFromCardInformation() throws IOException {
        //Given I am not a 2FA activated user
        User tester = motApi.user.createNon2FaTester(siteData.createSite().getId());

        //When I log into the application
        motUI.loginExpectingCardInformationPage(tester).clickContinueToHomeLink();

        //And continue to my homepage

        //Then my login is successful
        assertThat("Login Successful", motUI.isLoginSuccessful(), is(true));
    }

    @Test(groups = {"2fa"})
    public void userCannotActivateInvalidCard() throws IOException {
        //Given I am on the 2fa Activation Page after login in
        User tester = motApi.user.createNon2FaTester(siteData.createSite().getId());

        //When I activate with invalid card details
        motUI.authentication.securityCard.activateInvalid2faCard(tester, "INV12345", "00000");

        //Then my Activation is unsuccessful
        assertThat("Activation NOT Successful", motUI.authentication.isValidationSummaryDisplayed(), is(true));
    }

    @Test(groups = {"2fa"})
    public void userCanLogInAfterActivation() throws IOException {
        //Given am logged out of my session as a two factor user
        User twoFactorUser = motApi.user.createNon2FaTester(siteData.createSite().getId());
        motUI.authentication.securityCard.activate2faCard(twoFactorUser);
        motUI.logout(twoFactorUser);

        // When I login with my two factor pin
        motUI.loginExpectingPinEntryPage(twoFactorUser);
        motUI.authentication.enterPinAndSubmit(twoFactorUser.getTwoFactorPin());

        //Then my login is successful
        assertThat("Login Successful", motUI.isLoginSuccessful(), is(true));
    }

    @Test(groups = {"roles"})
    public void dvsaUsersWithTradeRoleNotShownActivationInformationScreen() throws IOException {
        //Given I am DVSA user with Trade Role
        User dvsaUser = motApi.user.createUserAsAreaOfficeOneUser("haveTradeRole");
        RoleManager.addSiteRole(dvsaUser, siteData.createSite().getId(), TradeRoles.TESTER);

        //When I login to the App
        motUI.login(dvsaUser);

        //Then I should NOT be presented with a 2FA login screen
        assertThat(motUI.isLoginSuccessful(), is(true));
    }

    @Test(testName = "2faHardStopDisabled", groups = {"2fa"})
    public void nonRegistered2faUsersDoNotSeeCardInformationPageTwice() throws IOException {
        //Given I am test user who has already seen the card information page
        User testUser = motApi.user.createNon2FaTester(siteData.createSite().getId());
        motUI.loginExpectingCardInformationPage(testUser);

        //When I log out
        motUI.logout(testUser);

        // And log into the app again
        motUI.login(testUser);

        //Then I should NOT be presented with the card information screen but the user's homepage
        assertThat(motUI.isLoginSuccessful(), is(true));
    }

    @Test(groups = {"BVT"})
    public void userWithInvalidLoginCredentialsShownErrorOnLoginPage() throws IOException {
        step("Given I login with invalid credentials");
        LoginPage loginPage = motUI.loginWithInvalidCredentials("invalidUser", "fakePassword");

        step("Then my login will not be successful and I should see error message on the login screen");
        assertThat(loginPage.getTitle(), is("MOT testing service"));
        assertThat(loginPage.isValidationSummaryDisplayed(), is(true));
    }

    @Test(testName = "2faHardStop", groups = {"2fa"})
    public void loginTradeUserNot2faActivatedShouldDisplay2faHardStopPage() throws IOException {
        step("Given I am a non 2fa activated Trade User ");
        User non2faUser = motApi.user.createNon2FaTester(siteData.createSite().getId());

        step("When I login to the system");
        motUI.loginExpecting2faHardStopPage(non2faUser);

        step("Then I should be redirected to 2fa HardStop Page");
        assertThat("/register-card/hard-stop", isPresentInCurrentUrl());
    }
}
