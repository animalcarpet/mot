package uk.gov.dvsa.ui.feature.journey;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.service.FeaturesService;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.BaseTest;
import uk.gov.dvsa.ui.pages.helpdesk.HelpDeskUserProfilePage;

import java.io.IOException;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;
import static org.testng.Assert.assertFalse;
import static org.testng.Assert.assertTrue;

public class HelpDeskTests extends BaseTest {

    private User tester;
    private Site testSite;
    private AeDetails aeDetails;
    private String randomName = RandomDataGenerator.generateRandomString(5, System.nanoTime());
    FeaturesService service = new FeaturesService();

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = userData.createTester(testSite.getId());
    }

    @Test (groups = {"BVT", "Regression"})
    public void successfullyUpdateAUsersEmailAddress() throws IOException {
        User csco = userData.createCustomerServiceOfficer(false);
        User bob = userData.createAedm(false);
        String email = RandomDataGenerator.generateEmail(15);

        //Given that I am on Bob's profile page as a Customer Service Centre Operative
        HelpDeskUserProfilePage helpDeskUserProfilePage =
                pageNavigator.goToUserHelpDeskProfilePage(csco, bob.getId());

        //When I update Bob's email address
        helpDeskUserProfilePage.updateEmailSuccessfully(email);

        //Then Bob's email is updated successfully
        helpDeskUserProfilePage.isEmailUpdateSuccessful(email);
    }

    @Test(groups = {"Regression", "VM-11326"},
            description = "Test that validates the authorised DVSA user can see authentication method of user",
            dataProvider = "createDvsaUser")
    public void testAuthenticationMethodIsDisplayedForDvsaUser(User dvsaUser) throws IOException {
        isAuthenticationMethodDisplayedForUser(dvsaUser, tester);
    }

    private void isAuthenticationMethodDisplayedForUser(User dvsaUser, User tester) throws IOException {
        HelpDeskUserProfilePage helpDeskUserProfilePage =
                pageNavigator.goToUserHelpDeskProfilePage(dvsaUser, tester.getId());
        if (service.getToggleValue("2fa.method.visible")) {
            assertThat(helpDeskUserProfilePage.isPersonAuthenticationMethodIsDisplayed(), is(true));
        } else {
            assertThat(helpDeskUserProfilePage.isPersonAuthenticationMethodIsDisplayed(), is(false));
        }
    }

    @DataProvider(name = "createDvsaUser")
    private Object[][] createDvsaUser() throws IOException {
        return new Object[][]{{userData.createVehicleExaminer(randomName, false)},
                {userData.createCustomerServiceOfficer(false)},
                {userData.createSchemeUser(false)},
                {userData.createAreaOfficeOne(randomName)},
                {userData.createAreaOfficeTwo(randomName)}};
    }
}