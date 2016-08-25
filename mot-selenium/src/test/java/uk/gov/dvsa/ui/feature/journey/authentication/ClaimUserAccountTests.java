package uk.gov.dvsa.ui.feature.journey.authentication;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.ui.DslTest;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class ClaimUserAccountTests extends DslTest {

    AeDetails aeDetails;
    Site testSite;

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws Exception {
         aeDetails = aeData.createAeWithDefaultValues();
         testSite = siteData.createNewSite(aeDetails.getId(), "My_Site");
    }


    @DataProvider(name = "createTester")
    public Object[][] createTesterAndVehicle() throws IOException {


        return new Object[][]{
                {userData.createTester(testSite.getId(), true)},
                {userData.createCustomerServiceOfficer(true)},
                {userData.createAedm(true)},
        };
    }

    @Test(groups = {"BVT"}, description = "VM-10319 - Tester, CSCO, AEDM can Claim Account and Set Password",
    dataProvider = "createTester")
    public void whenIclaimAccountAsUserIShouldSeePin(User user) throws Exception {

        // Given I claim my account as non 2fa user
        motUI.claimAccount.claimAsUser(user);

        // Then I should see pin
        assertThat(motUI.claimAccount.isPinDisplayed(), is(true));

    }

    @Test(testName = "2fa", groups = {"BVT", "Regression"}, description="2Fa user should see different confirmation page for claim account")
    public void whenIclaimAccountAs2FaUserIshouldNotSeePin() throws Exception {

        // Given I am 2FA user
        User twoFaUser = userData.createTester(testSite.getId());
        motUI.authentication.securityCard.activate2faCard(twoFaUser);
        motUI.logout(twoFaUser);

        // And I need to claim my account
        userData.requireClaimAccount(twoFaUser);

        // When I claim account
        motUI.claimAccount.claimAs2FaUser(twoFaUser);

        // Then I should not see pin
        assertThat(motUI.claimAccount.isPinDisplayed(), is(false));
    }
}

