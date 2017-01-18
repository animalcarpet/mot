package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

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

    @Test(testName = "2faHardStopDisabled", groups = {"BVT"}, description = "VM-10319 - Tester, CSCO, AEDM can Claim Account and Set Password")
    public void whenIClaimAccountAsUserIShouldSeePin() throws Exception {

        // Given I claim my account as non 2fa user
        motUI.claimAccount.claimAsUser(motApi.user.createNon2FaTester(testSite.getId(), true));

        // Then I should see pin
        assertThat(motUI.claimAccount.isPinDisplayed(), is(true));
    }

    @Test(groups = {"Regression"}, description="2Fa user should see different confirmation page for claim account")
    public void whenIClaimAccountAs2FaUserIShouldNotSeePin() throws Exception {

        // Given I am 2FA user
        User twoFaUser = motApi.user.createTester(testSite.getId());

        // And I need to claim my account
        motApi.user.requireClaimAccount(twoFaUser);

        // When I claim account
        motUI.claimAccount.claimAs2FaUser(twoFaUser);

        // Then I should not see pin
        assertThat(motUI.claimAccount.isPinDisplayed(), is(false));
    }
}

