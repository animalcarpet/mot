package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.accountclaim.TwoFaAccountClaimConfirmationPage;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ClaimUserAccountTests extends DslTest {

    private AeDetails aeDetails;
    private Site testSite;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws Exception {
         aeDetails = aeData.createAeWithDefaultValues();
         testSite = siteData.createNewSite(aeDetails.getId(), "My_Site");
    }

    @Test(groups = {"2fa", "BL-3024"})
    public void testResetAccountSecurity() throws Exception {
        // Given I am any 2FA user
        User twoFaUser = motApi.user.createTester(testSite.getId());

        // And I need to reset my account security
        motApi.user.requireClaimAccount(twoFaUser);

        // When I reset account security
        TwoFaAccountClaimConfirmationPage claimConfirmationPage = motUI.claimAccount.claimAs2FaUser(twoFaUser);

        // Then the reset account security confirmation page should be displayed
        assertThat(claimConfirmationPage.isConfirmationPage(), is(true));

        // Then I should be able to navigate to the sign in page
        claimConfirmationPage.goToSignIn();
    }
}

