package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class ReplacementCardTests extends DslTest {

    @Test(testName = "2fa", groups = {"BVT"})
    public void activateLinkIsDisplayedAfterReplacementCardOrder() throws IOException {
        step("Given Bob has ordered a replacement card");
        User bob = userData.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.activate2faCard(bob).logOut(bob);
        motUI.authentication.securityCard.signInWithoutSecurityCardAndOrderCard(bob);

        step("When I view my profile");
        motUI.profile.viewYourProfile(bob).activateCardLink();

        step("Then I can activate my card");
        assertThat("Card Activation is successful",
            motUI.authentication.securityCard.activate2faCard(bob).getConfirmationText(),
            containsString("Your security card has been activated"));
    }
}
