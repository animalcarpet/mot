package uk.gov.dvsa.module;

import uk.gov.dvsa.data.SiteData;
import uk.gov.dvsa.data.UserData;
import uk.gov.dvsa.data.VehicleData;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.ReasonForVehicleRefusal;
import uk.gov.dvsa.domain.model.vehicle.DvlaVehicle;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
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
    }

    public void startTestConfirmationPage(User user, DvlaVehicle dvlaVehicle) throws IOException, URISyntaxException {
        confirmationPage = pageNavigator.goToStartTestConfirmationPage(user, dvlaVehicle);
    }

    public void changeClass(String classNumber) {
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

    public CreateNewVehicleRecordConfirmPage createNewVehicleRecord(User tester, Vehicle vehicle) throws IOException, URISyntaxException {

        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage = pageNavigator.gotoCreateNewVehicleRecordIdentificationPage(tester);
        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);

        CreateNewVehicleRecordSpecificationPage createNewVehicleRecordSpecificationPage = createNewVehicleRecordIdentificationPage.submit();
        createNewVehicleRecordSpecificationPage.enterVehicleDetails(vehicle);

        CreateNewVehicleRecordConfirmPage createNewVehicleRecordConfirmPage = createNewVehicleRecordSpecificationPage.submit();

        if (createNewVehicleRecordConfirmPage.isDeclarationTextDisplayed()) {
            assertThat(createNewVehicleRecordConfirmPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        }

        return createNewVehicleRecordConfirmPage;
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

    public String addManualAdvisoryWithProfaneDescription(String description) {
        ManualAdvisoryModalPage advisoryModalPage = testResultsEntryPage.clickAddFRFButton().addManualAdvisory();
        return advisoryModalPage.addAdvisoryWithProfaneDescription(description).getValidationMessage();
    }

    public String getVehicleWeight() {
        return new StartTestConfirmationPage(driver).getVehicleWeight();
    }

    public String getVin() {
        return new StartTestConfirmationPage(driver).getVin();
    }

    public String getRegistration() {
        return new StartTestConfirmationPage(driver).getRegistration();
    }

    public boolean createNewDvsaVehicle(User tester, Vehicle vehicle) throws IOException, URISyntaxException {

        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
            gotoCreateNewVehicleRecordIdentificationPage(tester);

        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        CreateNewVehicleRecordSpecificationPage createNewVehicleRecordSpecificationPage =
            createNewVehicleRecordIdentificationPage.submit();

        createNewVehicleRecordSpecificationPage.enterVehicleDetails(vehicle);
        CreateNewVehicleRecordConfirmPage createNewVehicleRecordConfirmPage =
                createNewVehicleRecordSpecificationPage.submit();

        MotTestStartedPage motTestStartedPage = createNewVehicleRecordConfirmPage.setOneTimePassword("123456").startTest();

        if( motTestStartedPage.getModel().toLowerCase().contains(vehicle.getModel().toLowerCase())
                && motTestStartedPage.getVrm().toLowerCase().contains(vehicle.getDvsaRegistration().toLowerCase()) ) {
            return true;
        }else{
            return false;
        }
    }

    public CreateNewVehicleRecordIdentificationPage gotoCreateNewVehicleRecordIdentificationPage(
            User tester) throws IOException, URISyntaxException {

        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage =
                pageNavigator.gotoCreateNewVehicleRecordIdentificationPage(tester);

        return createNewVehicleRecordIdentificationPage;
    }

    public CreateNewVehicleRecordSpecificationPage submitValidPageOneDetails(
        CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage) {

        Vehicle vehicle = Vehicle.getAcceptableVehicle();

        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        return createNewVehicleRecordIdentificationPage.submit();
    }

    public boolean submitInvalidPageOneDate(String date, String errorMsg,
             CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage) {

        Vehicle vehicle = Vehicle.getAcceptableVehicle();

        vehicle.setFirstUsedDate(date);
        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        createNewVehicleRecordIdentificationPage.submit();

        return createNewVehicleRecordIdentificationPage.isErrorMessageDisplayed(errorMsg);
    }

    public boolean submitInvalidPageOneDetails(String property, String errorMsg,
             CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage) {

        Vehicle vehicle = resetVehicleProperty(property, Vehicle.getAcceptableVehicle());

        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        createNewVehicleRecordIdentificationPage.submit();

        return createNewVehicleRecordIdentificationPage.isErrorMessageDisplayed(errorMsg);
    }

    public boolean submitInvalidPageTwoDetails(
        String property, String errorMsg,
            CreateNewVehicleRecordSpecificationPage createNewVehicleRecordSpecificationPage) {

        Vehicle vehicle = resetVehicleProperty(property, Vehicle.getAcceptableVehicle());

        createNewVehicleRecordSpecificationPage.enterVehicleDetails(vehicle);
        createNewVehicleRecordSpecificationPage.submitInvalidFormDetails();

        return createNewVehicleRecordSpecificationPage.isErrorMessageDisplayed(errorMsg);
    }

    private Vehicle resetVehicleProperty(String property, Vehicle vehicle) {

        switch(property) {
            case "Country":
                vehicle.setCountryOfRegistration("");
                break;
            case "Registration":
                vehicle.setRegistration("");
                break;
            case "Vin":
                vehicle.setVin("");
                break;
            case "Make":
                vehicle.setMake("");
                break;
            case "Date":
                vehicle.setFirstUsedDate("");
                break;
            case "Transmission":
                vehicle.setTransmissionType("");
                break;
            case "Fuel":
                vehicle.setFuelType("");
                break;
            case "Model":
                vehicle.setModel("");
                break;
            case "Class":
                vehicle.setVehicleClass("");
                break;
            case "Cylinder":
                vehicle.setCylinderCapacity("");
                break;
            case "Primary":
                vehicle.setColour("");
                break;
        }

        return vehicle;
    }

}
