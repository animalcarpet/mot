package uk.gov.dvsa.module;

import org.openqa.selenium.NoSuchElementException;
import uk.gov.dvsa.data.SiteData;
import uk.gov.dvsa.data.UserData;
import uk.gov.dvsa.data.VehicleData;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.ReasonForVehicleRefusal;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.AssertionHelper;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;
import uk.gov.dvsa.ui.pages.mot.*;
import uk.gov.dvsa.ui.pages.mot.modal.ManualAdvisoryModalPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordConfirmPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordIdentificationPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.CreateNewVehicleRecordSpecificationPage;

import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;

public class NormalTest {

    private PageNavigator pageNavigator = null;
    private MotAppDriver driver;
    private String testStatus;
    private TestResultsEntryPage testResultsEntryPage;
    private StartTestConfirmationPage confirmationPage;
    private EnforcementTestSummaryPage enforcementSummaryPage;
    private String expectedText;
    private boolean declarationSuccessful = false;

    private static final String DECLARATION_STATEMENT = "I confirm that this MOT transaction has been conducted in accordance with " +
            "the conditions of authorisation which includes compliance with the MOT testing guide, the requirements for " +
            "authorisation, the appropriate MOT Inspection Manual and any other instructions issued by DVSA.";

    public NormalTest(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
        driver = pageNavigator.getDriver();
    }

    public TestCompletePage conductTestPass(User tester, Vehicle vehicle) throws IOException, URISyntaxException {
        TestResultsEntryPage testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);
        testResultsEntryPage.completeTestDetailsWithPassValues();
        TestSummaryPage testSummaryPage = testResultsEntryPage.clickReviewTestButton();

        if (testSummaryPage.isDeclarationTextDisplayed()) {
            assertThat(testSummaryPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        }
        return testSummaryPage.finishTestAndPrint();
    }

    public boolean isDeclarationStatementDisplayed() {
        return declarationSuccessful;
    }

    public void cancelTestWithReason(CancelTestReason reason) {
        ReasonToCancelTestPage cancelTestPage = testResultsEntryPage.clickCancelTest();
        cancelTestPage.enterReason(reason);

        if (cancelTestPage.isDeclarationTextDisplayed()) {
            assertThat(cancelTestPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        }
    }

    public String startTest() throws IOException, URISyntaxException {
        User tester = new UserData().createTester(new SiteData().createSite().getId());

        testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(
                tester, new VehicleData().getNewVehicle(tester));

        return setMotId();
    }

    private String setMotId() throws URISyntaxException {
        URI uri = new URI(driver.getCurrentUrl());
        String path = uri.getPath();

        return path.substring(path.lastIndexOf('/') + 1);
    }

    public void changeVehicleDetails(User tester, Vehicle vehicle) throws IOException, URISyntaxException {
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);
        StartTestConfirmationPage startTestConfirmationPage = vehicleSearchPage.searchVehicle(vehicle).selectVehicleForTest();
        VehicleDetailsChangedPage vehicleDetailsChangedPage = startTestConfirmationPage.changeVehicleDetailAndSubmit(vehicle);

        setDeclarationStatementStatus(vehicleDetailsChangedPage);
    }

    private void setDeclarationStatementStatus(VehicleDetailsChangedPage vehicleDetailsChangedPage) {
        if (vehicleDetailsChangedPage.isDeclarationTextDisplayed()) {
            assertThat(vehicleDetailsChangedPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        }
    }

    public void startTestConfirmationPage(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        confirmationPage = pageNavigator.goToStartTestConfirmationPage(user, vehicle);
        expectedText = confirmationPage.getVehicleWeight();
    }

    public void changeClass(String classNumber){
        VehicleDetailsChangedPage changedPage = confirmationPage.selectClass(classNumber)
                .clickStartMotTest(VehicleDetailsChangedPage.class);
        setDeclarationStatementStatus(changedPage);
    }

    public TestSummaryPage conductTrainingTest(User tester, Vehicle vehicle) throws IOException, URISyntaxException {
        TestResultsEntryPage testResultsEntryPage = pageNavigator.gotoTrainingTestResultsEntryPage(tester, vehicle);
        testResultsEntryPage.completeTestDetailsWithPassValues();
        TestSummaryPage testSummaryPage = testResultsEntryPage.clickReviewTestButton();

        declarationSuccessful = testSummaryPage.isDeclarationElementPresentInDom();

        return testSummaryPage;
    }

    public TestCompletePage finishTrainingTest(TestSummaryPage testSummaryPage) throws IOException, URISyntaxException {
         return testSummaryPage.clickFinishButton();
    }

    public void refuseToTestVehicle(User tester, Vehicle vehicle, ReasonForVehicleRefusal reason) throws IOException, URISyntaxException {
        RefuseToTestPage refuseToTestPage = pageNavigator.gotoRefuseToTestPage(tester, vehicle);
        refuseToTestPage.selectReason(reason);

        declarationSuccessful = refuseToTestPage.isDeclarationElementPresentInDom();
    }

    public void createNewVehicleRecord(User tester, Vehicle vehicle) throws IOException, URISyntaxException {

        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage = pageNavigator.gotoCreateNewVehicleRecordIdentificationPage(tester);
        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);

        CreateNewVehicleRecordSpecificationPage createNewVehicleRecordSpecificationPage = createNewVehicleRecordIdentificationPage.submit();
        createNewVehicleRecordSpecificationPage.enterVehicleDetails(vehicle);

        CreateNewVehicleRecordConfirmPage createNewVehicleRecordConfirmPage = createNewVehicleRecordSpecificationPage.submit();

        if (createNewVehicleRecordConfirmPage.isDeclarationTextDisplayed()) {
            assertThat(createNewVehicleRecordConfirmPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        }
    }

    public void viewTestAs(User user, String motTestId) throws IOException, URISyntaxException {
        String path = String.format(EnforcementTestSummaryPage.PATH, motTestId);
        enforcementSummaryPage = pageNavigator.navigateToPage(user, path, EnforcementTestSummaryPage.class);
        testStatus = enforcementSummaryPage.getTestStatus();
    }

    public void abortAsVe() {
        EnforcementAbortPage abortPage = enforcementSummaryPage.abort(EnforcementAbortPage.class);
        abortPage.enterReasonForAbort("This is a test of testing aborting a test").clickConfirm();
    }

    public String getTestStatus() {
        return testStatus;
    }

    public boolean isTextPresent(String actual) throws NoSuchElementException {
        return AssertionHelper.compareText(expectedText, actual);
    }

    public String addManualAdvisoryWithProfaneDescription(String description) {
        ManualAdvisoryModalPage advisoryModalPage = testResultsEntryPage.clickAddFRFButton().addManualAdvisory();
        return advisoryModalPage.addAdvisoryWithProfaneDescription(description).getValidationMessage();
    }
}
