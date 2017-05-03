package uk.gov.dvsa.ui.feature.journey.authentication.securitycard;

import org.hamcrest.core.Is;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.containsString;

public class ReplacementCardTests extends DslTest {

    @Test(groups = {"2fa"})
    public void securityCardSectionIsNotDisplayedAfterReplacementCardOrder() throws IOException {
        step("Given Paddy has ordered a replacement card");
        User paddy = motApi.user.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.signInWithoutSecurityCard(paddy);
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(paddy);

        step("When I view my profile");
        motUI.profile.viewYourProfile(paddy);

        step("Security Card Panel is not displayed");
        assertThat(motUI.profile.page().isSecurityCardPanelDisplayed(), Is.is(false));
    }

    @Test(groups = {"2fa"})
    public void activateLinkIsDisplayedAfterReplacementCardOrder() throws IOException {
        step("Given Bob has ordered a replacement card");
        User bob = motApi.user.createTester(siteData.createSite().getId());
        motUI.authentication.securityCard.signInWithoutSecurityCard(bob);
        motUI.authentication.securityCard.orderSecurityCardWithHomeAddress(bob);

        step("When I view my profile");
        motUI.profile.viewYourProfile(bob).activateCardLink();

        step("Then I can activate my card");
        assertThat("Card Activation is successful",
            motUI.authentication.securityCard.activate2faCard(bob).getConfirmationText(),
            containsString("Your security card has been activated"));
    }
}
