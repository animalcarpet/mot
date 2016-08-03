package uk.gov.dvsa.ui.feature.journey.mot.brake_test;

import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;

import java.io.IOException;
import java.net.URISyntaxException;

public class BrakeTestResultsTest extends DslTest {

    private Site site;
    private AeDetails aeDetails;
    private User tester;
    private Vehicle vehicle;

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = userData.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT"},
            description = "Verify that the brake test journey can be performed through the new MOT Test results page")
    public void saveBrakeTestResultsHappyPath() throws IOException, URISyntaxException {

        // Given I am on the Test Results Entry Page
        TestResultsEntryNewPage testResultsEntryNewPage = pageNavigator.gotoTestResultsEntryNewPage(tester,vehicle);

        // When I complete all Brake test Values with passing data
        testResultsEntryNewPage.completeBrakeTestWithPassValues();

        // Then I should see a fail on the test result page
//        assertThat(testResultsEntryNewPage.isPassNoticeDisplayed(), is(true));

        // Then I should be able to complete the Test Successfully
//        TestSummaryPage testSummaryPage = testResultsEntryNewPage.clickReviewTestButton();
//
//        TestCompletePage testCompletePage = testSummaryPage.finishTestAndPrint();
//
//        assertThat(testCompletePage.verifyBackToHomeLinkDisplayed(), is(true));
    }
}