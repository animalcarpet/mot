package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;


public class ProfilePageResetPinTests extends DslTest {


    @Test(groups = {"2fa", "BL-1571"},
            testName = "2faHardStopDisabled",
            description = "Test that a non-2fa user can reset their pin from profile")
    public void non2faUserCanResetPinViaProfile() throws IOException {
        // Given I am a trade user who has not activated 2FA
        User tester = motApi.user.createNon2FaTester(siteData.createSite().getId());

        // When I navigate to my user profile
        motUI.profile.viewYourProfile(tester);

        // Then I should be able to reset my pin via the link
        assertThat(motUI.profile.page().isResetPinLinkDisplayed(), is(true));
    }

    @Test(groups = {"2fa", "BL-1571"},
            description = "Test that a 2fa user can not reset their pin from profile")
    public void twoFaActiveUserCanNotResetPinViaProfile() throws IOException {
        // Given I am a user who has activated a 2FA card
        User twoFactorUser = motApi.user.createTester(siteData.createSite().getId());

        //When I navigate to my user profile
        motUI.profile.viewYourProfile(twoFactorUser);

        // Then I should not be able to reset my pin via the link
        assertThat(motUI.profile.page().isResetPinLinkDisplayed(), is(false));
    }
}
