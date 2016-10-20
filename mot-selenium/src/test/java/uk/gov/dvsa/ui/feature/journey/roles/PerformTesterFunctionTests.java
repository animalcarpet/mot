package uk.gov.dvsa.ui.feature.journey.roles;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.*;
import uk.gov.dvsa.ui.pages.profile.PersonProfilePage;

import java.io.IOException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;
import static org.hamcrest.core.IsEqual.equalTo;

public class PerformTesterFunctionTests extends DslTest {

    private User tester;
    private Site testSite;
    private AeDetails aeDetails;

    @BeforeMethod(alwaysRun = true)
    private void setUp() throws IOException {
        aeDetails = aeData.createNewAe("My_Test_AE", 100);
        testSite = siteData.createNewSite(aeDetails.getId(), "Test_Site");
        tester = motApi.user.createTester(testSite.getId());
    }

    //TODO Rewrite test according new person profile functionality
    @Test (groups = {"BVT"}, enabled = false)
    public void editDetailsWithoutNeedingApproval() throws Exception {
        String newEmail = "email@domaingreat.com";
        String postCode = "BS33 5TT";

        //Given I am logged as a Tester and I am on the ChangeDetails page
        ChangeDetailsPage changeDetailsPage = pageNavigator.navigateToPage(tester, ChangeDetailsPage.PATH, ChangeDetailsPage.class);

        //When I edit my postcode and email details
        changeDetailsPage.editPostCode(postCode).editEmailAndConfirmEmail(newEmail, newEmail);

        //Then I should be able to save with entering my pin
        PersonProfilePage personProfilePage = changeDetailsPage.update();

       //And my details should be changed
        assertThat(personProfilePage.verifyPostCodeIsChanged(postCode), is(true));
        assertThat(personProfilePage.verifyEmailIsChanged(newEmail), is(true));
    }

    @Test (groups = {"Regression"})
    public void viewPerformanceDashboard() throws Exception {

        //Given I have done only 1 mot test
        motApi.createTest(tester, testSite.getId(),
                vehicleData.getNewVehicle(tester), TestOutcome.PASSED, 14000, DateTime.now());

        //When I navigate to my performance dashboard page
        PerformanceDashBoardPage performanceDashBoardPage =
                pageNavigator.navigateToPage(tester, PerformanceDashBoardPage.PATH, PerformanceDashBoardPage.class);

        //Then I should see my test conducted is 1
        assertThat(performanceDashBoardPage.getTestConductedText(), equalTo("1"));

        //And passed test should be 1
        assertThat(performanceDashBoardPage.getPassedTestText(), equalTo("1"));
    }

    @Test (groups = {"BVT"})
    public void verifyTheCorrectAeAndVtsIsDisplayed() throws Exception {

        //Given I am logged as a Tester and I am on my homepage
        HomePage homePage = pageNavigator.navigateToPage(tester, HomePage.PATH, HomePage.class);

        //Then my AE should be My Test AE
        assertThat(homePage.getAeName(), equalTo( aeDetails.getAeName()));

        //And my Vts should be Test Site
        assertThat(homePage.getSiteName(), equalTo(testSite.getSiteNameAndNumberInHomePageFormat()));
    }

    @Test (groups = {"BVT"})
    public void myRoleIsDisplayedAsTester() throws Exception {

        //Given I am logged as a Tester and I am on my homepage
        HomePage homePage = pageNavigator.navigateToPage(tester, HomePage.PATH, HomePage.class);

        //I expect to see my role displayed
        assertThat(homePage.getRole(), equalTo("Tester".toUpperCase()));
    }
}
