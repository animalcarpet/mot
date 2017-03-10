package uk.gov.dvsa.ui.feature.journey.mot;

import com.jayway.restassured.response.Response;

import org.apache.http.HttpStatus;
import org.joda.time.DateTime;
import org.testng.annotations.BeforeClass;
import org.testng.annotations.BeforeMethod;
import org.testng.annotations.Test;

import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.AeDetails;
import uk.gov.dvsa.domain.model.Site;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.TestOutcome;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.helper.ReasonForRejection;
import uk.gov.dvsa.ui.DslTest;
import uk.gov.dvsa.ui.pages.mot.TestAbandonedPage;
import uk.gov.dvsa.ui.pages.mot.TestAbortedPage;
import uk.gov.dvsa.ui.pages.mot.TestCompletePage;
import uk.gov.dvsa.ui.pages.mot.TestOptionsPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryGroupAPageInterface;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryNewPage;
import uk.gov.dvsa.ui.pages.mot.TestResultsEntryPageInterface;
import uk.gov.dvsa.ui.pages.mot.TestSummaryPage;

import java.io.IOException;
import java.net.URISyntaxException;
import java.util.ArrayList;
import java.util.List;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;
import static org.hamcrest.core.Is.is;

public class ConductMotTests extends DslTest {

    private Site site;
    private User tester;
    private Vehicle vehicle;
    private List<ReasonForRejection> reasonForRejectionsList;
    private String defectName = "Horn control missing";

    @BeforeClass(alwaysRun = true)
    private void setupRfrList() {
        reasonForRejectionsList = new ArrayList<>();
        reasonForRejectionsList.add(ReasonForRejection.HORN_CONTROL_MISSING);
    }

    @BeforeMethod(alwaysRun = true)
    private void setupTestData() throws IOException {
        AeDetails aeDetails = aeData.createAeWithDefaultValues();
        site = siteData.createNewSite(aeDetails.getId(), "TestSite");
        tester = motApi.user.createTester(site.getId());
        vehicle = vehicleData.getNewVehicle(tester);
    }


    @Test(groups = {"BVT"})
    public void passTestSuccessfullyWithNoRFR() throws IOException, URISyntaxException {
        //Given I am on the Test Results Entry Page
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester,vehicle);

        //When I complete all test details with passing data
        testResultsEntryPage.completeTestDetailsWithPassValues(false);

        //Then I should see a pass on the test result page
        assertThat(testResultsEntryPage.isPassNoticeDisplayed(), is(true));

        //Then I should be able to complete the Test Successfully
        TestCompletePage testCompletePage = testResultsEntryPage.clickReviewTestButton().clickFinishButton(TestCompletePage.class);

        assertThat(testCompletePage.isReturnToHomepageLinkDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT"}, description = "Two Factor Authenticated users " +
            "should not be required to enter one time password")
    public void oneTimePasswordBoxNotDisplayedForTwoFactorAuthTester() throws IOException, URISyntaxException {

        //Given I am logged in as a tester authenticated by 2fa Card
        User twoFactorTester = motApi.user.createTester(site.getId());

        //When I complete an mot test
        motUI.normalTest.conductTestPass(twoFactorTester, vehicleData.getNewVehicle(twoFactorTester));

        //Then I should not see the PIN Box on test summary page
        assertThat(motUI.normalTest.isOneTimeInputBoxDisplayed(), is(false));
    }

    @Test(groups = {"BVT"})
    public void passSuccessfullyFloorBrakeTestWithLockBoxes() throws IOException, URISyntaxException {
        vehicle = vehicleData.getNewVehicle(tester, VehicleClass.one);
        TestResultsEntryGroupAPageInterface testResultsEntryPage;

        //Given I am on the Test Results Entry Page
        testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        //When I complete all test details with brake lock boxes checked
        testResultsEntryPage.completeTestWithFloorBrakeTestsWithLockBoxes();

        //Then I should see a pass on the test result page
        assertThat(testResultsEntryPage.isPassNoticeDisplayed(), is(true));

        //Then I should be able to complete the Test Successfully
        testResultsEntryPage.clickReviewTestButton().clickFinishButton(TestCompletePage.class);
    }

    @Test(groups = {"BVT"})
    public void passSuccessfullyRollerBrakeTestWithLockBoxes() throws IOException, URISyntaxException {
        vehicle = vehicleData.getNewVehicle(tester, VehicleClass.one);
        TestResultsEntryGroupAPageInterface testResultsEntryPage;

        //Given I am on the Test Results Entry Page
        testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, vehicle);

        //When I complete all test details with brake lock boxes checked
        testResultsEntryPage.completeTestWithRollerBrakeTestsWithLockBoxes();

        //Then I should see a pass on the test result page
        assertThat(testResultsEntryPage.isPassNoticeDisplayed(), is(true));

        //Then I should be able to complete the Test Successfully
        testResultsEntryPage.clickReviewTestButton().clickFinishButton(TestCompletePage.class);
    }

    @Test(groups = {"BVT"} )
    public void startAndAbandonTest() throws URISyntaxException, IOException {

        //Given I start a test and I am on the Test Results Page
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);

        //When I Abandon the test with a reason
        TestAbandonedPage testAbandonedPage =
                testResultsEntryPage.abandonMotTest(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I the test process should be cancelled and a VT30 Certificate generated message is displayed
        assertThat(testAbandonedPage.isVT30messageDisplayed(), is(true));
    }

    @Test(testName = "2fa", groups = {"BVT"} )
    public void startAndAbandonTest2FaActiveUser() throws URISyntaxException, IOException {
        //Given I am a 2FA activated user and I am on the Test Results Page
        User twoFactorUser = motApi.user.createTester(site.getId());
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(twoFactorUser, vehicle);

        //When I abandon the test with a reason
        TestAbandonedPage testAbandonedPage =
                testResultsEntryPage.abandonMotTest2FaActiveUser(CancelTestReason.DANGEROUS_OR_CAUSE_DAMAGE);

        //Then I the test process should be cancelled without needing a pin and a VT30 Certificate generated message is displayed
        assertThat(testAbandonedPage.isVT30messageDisplayed(), is(true));
    }

    @Test(groups = {"BVT"} )
    public void startAndAbortTestAsTester() throws URISyntaxException, IOException {

        //Given I start a test and I am on the Test Results Page
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);

        //When I Abort the test with a reason
        TestAbortedPage testAbortedPage = testResultsEntryPage.abortMotTest(CancelTestReason.TEST_EQUIPMENT_ISSUE);

        //Then the test process should be cancelled and a VT30 Certificate generated message is displayed
        assertThat(testAbortedPage.isVT30messageDisplayed(), is(true));
    }

    @Test(groups = {"BVT"} )
    public void startAndAbortTestAsVE() throws URISyntaxException, IOException {
        User vehicleExaminer = motApi.user.createVehicleExaminer("Default-VE", false);

        //Given I start a test as Tester
        String testId = motUI.normalTest.startTest(motApi.user.createTester(site.getId()));

        //When a Vehicle Examiner abort the test
        motUI.normalTest.viewTestAs(vehicleExaminer, testId);
        motUI.normalTest.abortAsVe();

        //Then the test is aborted successfully
        motUI.normalTest.viewTestAs(vehicleExaminer, testId);
        assertThat(motUI.normalTest.getTestStatus(), equalToIgnoringCase("Aborted by VE"));
    }

    @Test(groups = {"BVT"})
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
    public void printInspectionSheetSuccessfully() throws IOException, URISyntaxException {
        // GIVEN I start a new MOT test
        TestOptionsPage testStartedPage = vehicleReinspectionWorkflow().startMotTestAsATester(tester, vehicle);

        // THEN I can see vehicle inspection sheet download link
        assertThat(testStartedPage.printInspectionSheetIsDisplayed(), is(true));

        // AND WHEN I click on Vehicle inspection sheet link
        Response pdfResponse = frontendData.downloadFileFromFrontend(
            testStartedPage.getPrintInspectionSheetLink(),
            pageNavigator.getCurrentTokenCookie(),
            pageNavigator.getCurrentSessionCookie()
        );

        // THEN the PDF is successfully generated
        assertThat(HttpStatus.SC_OK, is(pdfResponse.getStatusCode()));
        assertThat("application/pdf", is(pdfResponse.getContentType()));
    }

    @Test(groups = {"regression"}, description = "Tester print a VIS after starting a MOT Test")
    public void printInspectionSheetFromTestResultsEntryIsDisplayed() throws IOException, URISyntaxException {
        step("Given I start a MOT Test");
        step("When I sign out and back in to complete the test");
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);

        step("Then the 'Print MOT inspection sheet' link is displayed ");
        assertThat(testResultsEntryPage.isVehicleInspectionSheetDisplayed(), is(true));
    }

    @Test (groups = {"Regression"})
    public void printDocumentButtonShouldNotBeDisplayedForDemoTest() throws IOException, URISyntaxException {

        // GIVEN I conducted a demo test as a new user
        TestSummaryPage summaryPage = motUI.normalTest.conductTrainingTest(motApi.user.createUserWithoutRole(), vehicle);

        // WHEN I complete it
        TestCompletePage testCompletePage = motUI.normalTest.finishTrainingTest(summaryPage);

        //THEN I should not be presented with
        assertThat(testCompletePage.isPrintDocumentButtonDisplayed(), is(false));
    }

    @Test(groups = {"BVT", "BL-1935"}, description = "Verifies that user is able to see test results entry page")
    public void motTestSummaryPage() throws IOException, URISyntaxException {
        // GIVEN I complete an mot test using testResultsEntryPage and see the test summary page
        TestSummaryPage testSummaryPage = pageNavigator.getTestSummaryPage(tester, vehicle);

        // WHEN I click the back to results entry link
        TestResultsEntryPageInterface testResultsEntryPage = testSummaryPage.clickBackToResultsEntryLink();

        //THEN I should be returned to testResultsEntryPage containing a review test button
        assertThat(testResultsEntryPage.isClickReviewTestButtonPresent(), is(true));
    }

    @Test(groups = {"BVT", "BL-3478"}, description = "Verifies that user is able to repair defect from new test results screen")
    public void repairDefectDuringRetestSuccessfully() throws IOException, URISyntaxException {
        //Given I have a vehicle with a failed MOT test
        motApi.createTestWithRfr(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now(), reasonForRejectionsList);

        //And fault has been fixed

        //When I conduct a retest on the vehicle and click repaired on defect
        TestResultsEntryNewPage testResultsEntryNewPage = ((TestResultsEntryNewPage)motUI.retest.startRetest(vehicle, tester))
                .clickRepaired(defectName, TestResultsEntryNewPage.class);

        //Then the defect should be marked as repaired and the undo link is displayed
        assertThat(testResultsEntryNewPage.isUndoLinkDisplayed(), is(true));
    }

    @Test(groups = {"BVT", "BL-1423"},
            description = "Verifies that user is able to repair defect from new test results screen and undo repair status")
    public void undoRepairDefectDuringRetestSuccessfully() throws IOException, URISyntaxException {
        //Given I have a vehicle with a failed MOT test
        motApi.createTestWithRfr(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now(), reasonForRejectionsList);

        //And fault has been fixed

        //When I conduct a retest on the vehicle click repaired on defect and click on undo link
        TestResultsEntryNewPage testResultsEntryNewPage = ((TestResultsEntryNewPage)motUI.retest.startRetest(vehicle, tester))
                .clickRepaired(defectName, TestResultsEntryNewPage.class)
                .clickUndoRepaired(TestResultsEntryNewPage.class);

        //Then the Mark as repaired button should be displayed
        assertThat(testResultsEntryNewPage.isMarkAsRepairedButtonDisplayed(defectName), is(true));
    }

    @Test(groups = {"BVT", "BL-1423"}, description = "Verifies that user is not able to see defect on a summary page after repairing it")
    public void statusOfRepairedDefectDuringRetestOnSummaryPage() throws IOException, URISyntaxException {
        //Given I have a vehicle with a failed MOT test
        motApi.createTestWithRfr(tester, site.getId(), vehicle, TestOutcome.FAILED, 12345, DateTime.now(), reasonForRejectionsList);

        //And fault has been fixed

        //When I conduct a retest on the vehicle click repaired on defect and click review test button
        TestSummaryPage testSummaryPage = ((TestResultsEntryNewPage)motUI.retest.startRetest(vehicle, tester))
                .completeTestDetailsWithPassValues(defectName, true);

        //Then the defect should not be displayed
        assertThat(testSummaryPage.isDefectDisplayed(defectName), is(false));
    }
}
