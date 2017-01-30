package uk.gov.dvsa.ui.feature.journey.account_administration;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.service.FeaturesService;
import uk.gov.dvsa.helper.RandomDataGenerator;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.helpdesk.HelpDeskUserProfilePage;

import java.io.IOException;

import static org.hamcrest.CoreMatchers.is;
import static org.hamcrest.MatcherAssert.assertThat;

public class HelpDeskTests extends DslTest {

    private User tester;
    private Site testSite;
    private AeDetails aeDetails;
    private String randomName = RandomDataGenerator.generateRandomString(5, System.nanoTime());

    @BeforeMethod(alwaysRun = true)
    public void setUp() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = motApi.user.createTester(testSite.getId());
    }

    @Test (groups = {"Regression"})
    public void successfullyUpdateAUsersEmailAddress() throws IOException {
        User csco = motApi.user.createCustomerServiceOfficer(false);
        User bob = motApi.user.createAedm(false);
        String email = RandomDataGenerator.generateEmail();

        //Given that I am on Bob's profile page as a Customer Service Centre Operative
        motUI.helpDesk.viewUserProfile(csco, bob.getId());

        //When I update Bob's email address
        motUI.helpDesk.page().updateEmailSuccessfully(email);

        //Then Bob's email is updated successfully
        motUI.helpDesk.page().isEmailUpdateSuccessful(email);
    }

    @DataProvider(name = "createDvsaUser")
    private Object[][] createDvsaUser() throws IOException {
        return new Object[][]{{motApi.user.createVehicleExaminer(randomName, false)},
                {motApi.user.createCustomerServiceOfficer(false)},
                {motApi.user.createSchemeUser(false)},
                {motApi.user.createAreaOfficeOne(randomName)},
                {motApi.user.createAreaOfficeTwo(randomName)}};
    }
}
