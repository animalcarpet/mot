package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class LostOrForgottenCardTests extends DslTest {

    @Test(testName = "2fa", groups = {"BVT"})
    public void iCanSignInWhenIForgetMySecurityCardAs2faUser() throws IOException {
        User twoFactorUser = userData.createTester(siteData.createSite().getId());

        step("Given that I am on the Security card PIN page");
        motUI.authentication.gotoTwoFactorPinEntryPage(twoFactorUser);

        step("When I complete the sign in journey");
        motUI.authentication.securityCard.signInWithoutSecurityCardLandingOnHomePage(twoFactorUser);

        step("Then I should see the homepage");
        assertThat("The User is on the Home Page", motUI.isLoginSuccessful(), is(true));
    }
}
