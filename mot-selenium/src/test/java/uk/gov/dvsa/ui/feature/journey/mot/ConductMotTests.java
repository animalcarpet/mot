package uk.gov.dvsa.ui.feature.journey.mot;

import org.joda.time.DateTime;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.*;

import java.io.IOException;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;
import static org.hamcrest.core.Is.is;

public class ConductMotTests extends DslTest {

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


    @Test(testName = "OldRFRTest", groups = {"BVT"})
    public void passTestSuccessfullyWithNoRFR() throws IOException, URISyntaxException {

        //Given I am on the Test Results Entry Page
        TestResultsEntryPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester,vehicle);

        //When I complete all test details with passing data
        testResultsEntryPage.completeTestDetailsWithPassValues();

        //Then I should see a pass on the test result page
        assertThat(testResultsEntryPage.isPassNoticeDisplayed(), is(true));

        //Then I should be able to complete the Test Successfully
        TestSummaryPage testSummaryPage = testResultsEntryPage.clickReviewTestButton();

        TestCompletePage testCompletePage = testSummaryPage.finishTestAndPrint();

        assertThat(testCompletePage.verifyBackToHomeLinkDisplayed(), is(true));
    }

    @Test(testName = "OldRFRTest", groups = {"BVT"} )
    public void startAndAbandonTest() throws URISyntaxException, IOException {

        //Given I start a test and I am on the Test Results Page
        TestResultsEntryPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);

        //When I Abandon the test with a reason
        TestAbandonedPage testAbandonedPage =
                testResultsEntryPage.abandonMotTest(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I the test process should be cancelled and a VT30 Certificate generated message is displayed
        assertThat(testAbandonedPage.isVT30messageDisplayed(), is(true));
    }

    @Test(testName = "OldRFRTest", groups = {"BVT"} )
    public void startAndAbortTestAsTester() throws URISyntaxException, IOException {

        //Given I start a test and I am on the Test Results Page
        TestResultsEntryPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);

        //When I Abort the test with a reason
        TestAbortedPage testAbortedPage = testResultsEntryPage.abortMotTest(CancelTestReason.TEST_EQUIPMENT_ISSUE);

        //Then the test process should be cancelled and a VT30 Certificate generated message is displayed
        assertThat(testAbortedPage.isVT30messageDisplayed(), is(true));
    }

    @Test(testName = "OldRFRTest", groups = {"BVT"} )
    public void startAndAbortTestAsVE() throws URISyntaxException, IOException {
        User vehicleExaminer = userData.createVehicleExaminer("Default-VE", false);

        //Given I start a test as Tester
        String testId = motUI.normalTest.startTest();

        //When a Vehicle Examiner abort the test
        motUI.normalTest.viewTestAs(vehicleExaminer, testId);
        motUI.normalTest.abortAsVe();

        //Then the test is aborted successfully
        motUI.normalTest.viewTestAs(vehicleExaminer, testId);
        assertThat(motUI.normalTest.getTestStatus(), equalToIgnoringCase("Aborted by VE"));
    }

    @Test(testName = "OldRFRTest", groups = {"BVT"})
    public void conductRetestSuccessfully() throws IOException, URISyntaxException {

        //Given I have a vehicle with a failed MOT test
        motApi.createTest(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now());

        //And all faults has been fixed

        //When I conduct a retest on the vehicle
        motUI.retest.conductRetestPass(vehicle, tester);

        //Then the retest is successful
        motUI.retest.verifyRetestIsSuccessful();
    }

    @Test(groups = {"BVT"})
    public void printInspectionSheetSuccessfully() throws IOException {
        TestOptionsPage page = vehicleReinspectionWorkflow().startMotTestAsATester(tester, vehicle);
        page.clickPrintInspectionSheet();
    }

    @Test (testName = "OldRFRTest", groups = {"Regression"})
    public void printDocumentButtonShouldNotBeDisplayedForDemoTest() throws IOException, URISyntaxException {

        // GIVEN I conducted a demo test as a new user
        TestSummaryPage summaryPage = motUI.normalTest.conductTrainingTest(userData.createUserWithoutRole(), vehicle);

        // WHEN I complete it
        TestCompletePage testCompletePage = motUI.normalTest.finishTrainingTest(summaryPage);

        //THEN I should not be presented with
        assertThat(testCompletePage.isPrintDocumentButtonDisplayed(), is(false));
    }

    @Test(testName = "OldRFRTest", groups = {"BVT", "BL-1935"}, description = "Verifies that user is able to see test results entry (old) page")
    public void motTestSummaryPageWithTestResultEntryImprovementsToggleOff() throws IOException, URISyntaxException {
        // GIVEN I complete an mot test using testResultsEntryPage and see the test summary page
        TestSummaryPage testSummaryPage = pageNavigator.getTestSummaryPage(tester, vehicle);

        // WHEN I click the back to results entry link
        TestResultsEntryPageInterface testResultsEntryOldPage = testSummaryPage.clickBackToResultsEntryLink();

        //THEN I should be returned to testResultsEntryPage containing a review test button
        assertThat(testResultsEntryOldPage.isClickReviewTestButtonPresent(), is(true));
    }

    @Test(testName = "TestResultEntryImprovements", groups = {"BVT", "BL-1935"}, description = "Verifies that user is able to see test results entry new page")
    public void motTestSummaryPageWithTestResultEntryImprovementsToggleOn() throws IOException, URISyntaxException {
        // GIVEN I complete an mot test using testResultsEntryNewPage and see the test summary page
        TestSummaryPage testSummaryPage = pageNavigator.getTestSummaryPage(tester, vehicle);

        // WHEN I click the back to results entry link
        TestResultsEntryPageInterface testResultsEntryNewPage = testSummaryPage.clickBackToResultsEntryLink();

        //THEN I should be returned to testResultsEntryNewPage containing a review test button
        assertThat(testResultsEntryNewPage.isClickReviewTestButtonPresent(), is(true));
    }

}
