package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class LostOrForgottenCardTests extends DslTest {

    @Test(groups = {"BVT"})
    public void iCanSignInWhenIForgetMySecurityCardAs2faUser() throws IOException {
        User twoFactorUser = motApi.user.createTester(siteData.createSite().getId());

        step("When I complete sign in via the lost and forgotten journey");
        motUI.authentication.securityCard.signInWithoutSecurityCardLandingOnHomePage(twoFactorUser);

        step("Then I should see the homepage");
        assertThat("The User is on the Home Page", motUI.isLoginSuccessful(), is(true));
    }

    @Test(groups = {"BVT"})
    public void userWithReplacementCardOrderedIsDirectedToAlreadyOrderedCardPage() throws IOException {
        User twoFactorUser = motApi.user.createTester(siteData.createSite().getId());

        step("Given I am logged out after ordering a card via lost/forgotten journey");
        motUI.authentication.securityCard.signInWithoutSecurityCardAndOrderCard(twoFactorUser);
        motUI.logout(twoFactorUser);

        step("When I login again within the same day");
        step("Then I am presented with the Already Ordered card landing page");
        motUI.loginExpecting2faAlreadyOrderedPage(twoFactorUser);

        step("Then I am able to complete the sign in journey");
        motUI.authentication.securityCard.signInExpectingFirstQuestionLostAndForgottenCardOrdered(twoFactorUser);

        assertThat("The User is on the Home Page", motUI.isLoginSuccessful(), is(true));
    }

    @Test(groups = {"BVT"})
    public void userWithReplacementCardOrderedAndActivatedDirectedTo2FAPinEntryPage() throws IOException {
        User twoFactorUser = motApi.user.createTester(siteData.createSite().getId());

        step("Given I am logged out after ordering a card via lost/forgotten journey");
        motUI.authentication.securityCard.signInWithoutSecurityCardAndOrderCard(twoFactorUser);
        motUI.logout(twoFactorUser);

        step("When I activate my card");
        motUI.loginExpecting2faAlreadyOrderedPage(twoFactorUser);
        motUI.authentication.securityCard.signInExpectingFirstQuestionLostAndForgottenCardOrdered(twoFactorUser);
        motUI.authentication.securityCard.activate2faCard(twoFactorUser);
        motUI.logout(twoFactorUser);

        step("Then I am able to complete the sign in journey by entering my security card pin");
        motUI.loginExpectingPinEntryPage(twoFactorUser);
        motUI.authentication.enterPinAndSubmit(twoFactorUser.getTwoFactorPin());

        assertThat("The User is on the Home Page", motUI.isLoginSuccessful(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT"})
    public void userDirectedToSecurityQuestionsOnSubsequentDailyLoginsAfterUsingLostForgottenJourney() throws IOException {

        step("Given I am 2FA active and logged in today using lost/forgotten journey");
        User twoFactorUser = motApi.user.createTester(siteData.createSite().getId());

        step("When I log in again within the same day");
        step("Then I am directed to answer my security questions");
        motUI.authentication.securityCard.signInExpectingFirstQuestionLostAndForgottenNoCard(twoFactorUser);

        step("And I am able to complete the sign in journey");
        assertThat("The User is on the Home Page", motUI.isLoginSuccessful(), is(true));
    }

}
