package uk.gov.dvsa.ui.feature.journey.mot;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.DataProvider;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.HomePage;
import uk.gov.dvsa.ui.pages.mot.GenerateSurveyReportsPage;
import uk.gov.dvsa.ui.pages.mot.GiveFeedbackPage;
import uk.gov.dvsa.ui.pages.mot.SurveyThankYouPage;
import uk.gov.dvsa.ui.pages.mot.TestCompletePage;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class MotSurveyTests extends DslTest {

    private AeDetails aeDetails;
    private Site site;
    private User tester;
    private Vehicle vehicle;

    private User schemeUser;

    private User areaOffice1User;
    private User vehicleExaminerUser;
    private User aedm;
    private User siteManager;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);

        schemeUser = userData.createSchemeUser(false);

        areaOffice1User = userData.createAreaOfficeOne("AreaOfficerOne");
        vehicleExaminerUser = userData.createVehicleExaminer("VehicleExaminer", false);
        aedm = userData.createAedm(aeDetails.getId(), "Test", false);
        siteManager = userData.createSiteManager(site.getId(), false);
    }

    @Test(testName = "Survey", groups = {"BL-1529"},
          description = "Verifies that user can navigate back from survey page to home page")
    public void backwardNavigationFromSurveyPageToHomePage() throws IOException, URISyntaxException {
        // Given I am on the Test Complete Page as a tester
        TestCompletePage testCompletePage = motUI.normalTest.conductTestPass(tester, vehicle);

        // When I click on the back home link I should be on the Give Feedback Page
        GiveFeedbackPage giveFeedbackPage = testCompletePage.clickBackHomeLink();

        // When I click on Cancel and return home link
        giveFeedbackPage.clickCancelAndReturnHome();

        // Then I should be redirected to the home page

    }

    @Test(testName = "Survey", groups = {"BL-1529"},
            description = "Verifies that user is redirected to home page after submitting empty survey")
    public void userRedirectedToHomePageOnSubmissionOfEmptySurvey() throws IOException, URISyntaxException {
        // Given I am on the Test Complete Page as a tester
        TestCompletePage testCompletePage = motUI.normalTest.conductTestPass(tester, vehicle);

        // When I click on the back home link I should be on the Give Feedback Page
        GiveFeedbackPage giveFeedbackPage = testCompletePage.clickBackHomeLink();

        // When I submit an empty survey
        giveFeedbackPage.submitFeedback(true, HomePage.class);

        // Then I should be redirected to the home page

    }

    @Test(testName = "Survey", groups = {"BL-1529"},
            description = "Verifies that user is able to submit feedback")
    public void userCanSubmitFeedback() throws IOException, URISyntaxException {
        // Given I am on the Test Complete Page as a tester
        TestCompletePage testCompletePage = motUI.normalTest.conductTestPass(tester, vehicle);

        // When I click on the back home link I should be on the Give Feedback Page
        GiveFeedbackPage giveFeedbackPage = testCompletePage.clickBackHomeLink();

        // When I submit some feedback and click on the Back Home link
        giveFeedbackPage.submitFeedback(false, SurveyThankYouPage.class)
                .clickBackHomeLink();

        // Then I should be redirected to the Home page

    }

    @Test(testName = "Survey", groups = {"BL-1531"},
            description = "Verifies that a valid user can navigate back from Survey Reports page to Home page")
    public void backwardNavigationFromSurveyReportsPageToHomePage() throws IOException, URISyntaxException {
        // Given I am on the Survey Reports page as a scheme user
        GenerateSurveyReportsPage generateSurveyReportsPage =
                pageNavigator.navigateToPage(schemeUser, GenerateSurveyReportsPage.PATH, GenerateSurveyReportsPage.class);

        // When I click on the Return Home link
        generateSurveyReportsPage.clickBackHomeLink();

        // Then I should be redirected to the Home page

    }

    @Test(testName = "Survey", groups = {"Regression", "BL-1531"},
            description = "Verifies that an invalid user cannot navigate to the Survey Reports page",
            dataProvider = "invalidUserProvider")
    public void testNonValidUserCannotNavigateToSurveyReportsPage(User user) throws IOException, URISyntaxException {
        // Given I am on the home page as an invalid user
        HomePage homePage = pageNavigator.gotoHomePage(user);

        // Then the Generate Survey Reports link shouldn't be displayed
        assertThat(homePage.isGenerateSurveyReportsLinkDisplayed(), is(false));
    }

    @Test(testName = "Survey", groups = {"BL-1531"},
            description = "Verifies that a user can download a survey report")
    public void userCanDownloadReport() throws IOException, URISyntaxException {
        // Given I am on the Survey Reports page as a scheme user
        GenerateSurveyReportsPage generateSurveyReportsPage =
                pageNavigator.navigateToPage(schemeUser, GenerateSurveyReportsPage.PATH, GenerateSurveyReportsPage.class);

        // When I click the first survey report download link
        generateSurveyReportsPage.clickFirstDownloadLink();

        // Then the survey report should download

    }

    @DataProvider(name = "invalidUserProvider")
    private Object[][] invalidUserProvider() {
        return new Object[][]{
                {areaOffice1User},
                {vehicleExaminerUser},
                {tester},
                {aedm},
                {siteManager}
        };
    }
}
