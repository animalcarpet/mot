package uk.gov.dvsa.journey;

import uk.gov.dvsa.data.SiteData;
import uk.gov.dvsa.data.UserData;
import uk.gov.dvsa.data.VehicleData;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.mot.ReasonForVehicleRefusal;
import uk.gov.dvsa.domain.model.vehicle.DvlaVehicle;
import uk.gov.dvsa.domain.api.response.Make;
import uk.gov.dvsa.domain.api.response.Model;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.api.response.FuelType;
import uk.gov.dvsa.domain.model.vehicle.VehicleFactory;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
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
    private TestResultsEntryGroupAPageInterface testResultsEntryPage;
    private StartTestConfirmationPage confirmationPage;
    private EnforcementTestSummaryPage enforcementSummaryPage;
    private boolean declarationSuccessful = false;
    private boolean declarationFor2FaSuccessful = false;
    private boolean isPinBoxDisplayed = false;

    private static final String DECLARATION_STATEMENT = "I confirm that this MOT transaction has been conducted in accordance with " +
            "the conditions of authorisation which includes compliance with the MOT testing guide, the requirements for " +
            "authorisation, the appropriate MOT Inspection Manual and any other instructions issued by DVSA.";

    private static final String DECLARATION_STATEMENT_2FA_SAVE_TEST = "By saving this test result you confirm that " +
            "you have carried out this MOT test in line with DVSA conditions for MOT testing.";

    private static final String DECLARATION_STATEMENT_2FA_CREATE_VEHICLE = "By saving this new vehicle record you confirm that the vehicle " +
            "can't be found in the vehicle search and that you have made the record in line with DVSA conditions for MOT testing.";

    private static final String DECLARATION_STATEMENT_2FA_CHANGE_VEHICLE_DETAILS = "By saving this vehicle record you confirm " +
            "that the changes you have made are in line with DVSA conditions for MOT testing.";

    private static final String DECLARATION_STATEMENT_2FA_CANCEL_TEST = "By cancelling this test you confirm that " +
            "you have carried the MOT test to the point of the cancellation in line with DVSA conditions for MOT testing.";

    public NormalTest(PageNavigator pageNavigator) {
        this.pageNavigator = pageNavigator;
        driver = pageNavigator.getDriver();
    }

    public TestSummaryPage conductTestPass(User tester, Vehicle vehicle) throws IOException, URISyntaxException {
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, vehicle);
        testResultsEntryPage.completeTestDetailsWithPassValues();
        TestSummaryPage testSummaryPage = testResultsEntryPage.clickReviewTestButton();

        if (testSummaryPage.isOneTimePasswordBoxDisplayed()) {
            assertThat(testSummaryPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
            isPinBoxDisplayed = true;
        } else {
            assertThat(testSummaryPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT_2FA_SAVE_TEST));
            declarationFor2FaSuccessful = true;
        }
        return testSummaryPage;
    }

    public boolean isDeclarationStatementDisplayed() {
        return declarationSuccessful;
    }

    public boolean isDeclarationStatementFor2FaDisplayed() {
        return declarationFor2FaSuccessful;
    }

    public boolean isOneTimeInputBoxDisplayed() {
        return isPinBoxDisplayed;
    }

    public void cancelTestWithReason(CancelTestReason reason) {
        ReasonToCancelTestPage cancelTestPage = testResultsEntryPage.clickCancelTest();
        cancelTestPage.enterReason(reason);

        if (cancelTestPage.isOneTimePasswordBoxDisplayed()) {
            assertThat(cancelTestPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
        } else {
            assertThat(cancelTestPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT_2FA_CANCEL_TEST));
        }

        declarationSuccessful = true;
    }

    public String startTest() throws IOException, URISyntaxException {
        User tester = new UserData().createTester(new SiteData().createSite().getId());

        return startTest(tester);
    }

    public String startTest(User tester) throws IOException, URISyntaxException {
        if (!ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            testResultsEntryPage = pageNavigator.gotoTestResultsEntryPage(tester, new VehicleData().getNewVehicle(tester));
        } else {
            testResultsEntryPage = pageNavigator.gotoTestResultsEntryNewPage(tester, new VehicleData().getNewVehicle(tester));
        }

        return setMotId();
    }

    private String setMotId() throws URISyntaxException {
        URI uri = new URI(driver.getCurrentUrl());
        String path = uri.getPath();

        return path.substring(path.lastIndexOf('/') + 1);
    }

    public VehicleDetailsChangedPage changeVehicleDetails(User tester, Vehicle vehicle) throws IOException, URISyntaxException {
        VehicleSearchPage vehicleSearchPage = pageNavigator.navigateToPage(tester, VehicleSearchPage.PATH, VehicleSearchPage.class);
        StartTestConfirmationPage startTestConfirmationPage = vehicleSearchPage.searchVehicle(vehicle).selectVehicle(StartTestConfirmationPage.class);
        VehicleDetailsChangedPage vehicleDetailsChangedPage = startTestConfirmationPage.changeVehicleDetailAndSubmit(vehicle);

        setDeclarationStatementStatus(vehicleDetailsChangedPage);

        return vehicleDetailsChangedPage;
    }

    private void setDeclarationStatementStatus(VehicleDetailsChangedPage vehicleDetailsChangedPage) {
        if (vehicleDetailsChangedPage.isPinBoxDisplayed()) {
            assertThat(vehicleDetailsChangedPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        } else {
            assertThat(vehicleDetailsChangedPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT_2FA_CHANGE_VEHICLE_DETAILS));
            declarationFor2FaSuccessful = true;
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
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTrainingTestResultsEntryPage(tester, vehicle);
        testResultsEntryPage.completeTestDetailsWithPassValues();
        TestSummaryPage testSummaryPage = testResultsEntryPage.clickReviewTestButton();

        declarationSuccessful = testSummaryPage.isDeclarationDisplayed();

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

        if (createNewVehicleRecordConfirmPage.isPinBoxDisplayed()) {
            assertThat(createNewVehicleRecordConfirmPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT));
            declarationSuccessful = true;
        } else {
            assertThat(createNewVehicleRecordConfirmPage.getDeclarationText(), equalToIgnoringCase(DECLARATION_STATEMENT_2FA_CREATE_VEHICLE));
            declarationFor2FaSuccessful = true;
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

    public boolean addManualAdvisoryWithProfaneDescriptionReturnsWarning(String description) {
        if (!ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            ManualAdvisoryModalPage advisoryModalPage = ((TestResultsEntryPage) testResultsEntryPage).clickAddFRFButton().addManualAdvisory();
            return advisoryModalPage.addAdvisoryWithProfaneDescription(description).isProfanityWarningDisplayed();
        } else {
            Defect.DefectBuilder builder = new Defect.DefectBuilder();
            builder.setDescription(description);
            Defect defect = builder.build();

            return ((TestResultsEntryNewPage) testResultsEntryPage).clickSearchForADefectButton().navigateToAddAManualAdvisory()
                    .fillDefectDescription(defect).clickAddDefectButtonExpectingFailure().isProfanityWarningDisplayed();
        }
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

        if( motTestStartedPage.getModel().toLowerCase().contains(vehicle.getModel().getName().toLowerCase())
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

        Vehicle vehicle = VehicleFactory.generateValidDetails();

        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        return createNewVehicleRecordIdentificationPage.submit();
    }

    public boolean submitInvalidPageOneDate(String date, String errorMsg,
             CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage) {

        Vehicle vehicle = VehicleFactory.generateValidDetails();

        vehicle.setFirstUsedDate(date);
        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        createNewVehicleRecordIdentificationPage.submit();

        return createNewVehicleRecordIdentificationPage.isErrorMessageDisplayed(errorMsg);
    }

    public boolean submitInvalidPageOneDetails(String property, String errorMsg,
             CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage) {

        Vehicle vehicle = resetVehicleProperty(property, VehicleFactory.generateValidDetails());

        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        createNewVehicleRecordIdentificationPage.submit();

        return createNewVehicleRecordIdentificationPage.isErrorMessageDisplayed(errorMsg);
    }

    public boolean submitInvalidPageTwoDetails(
        String property, String errorMsg,
            CreateNewVehicleRecordSpecificationPage createNewVehicleRecordSpecificationPage) {

        Vehicle vehicle = resetVehicleProperty(property, VehicleFactory.generateValidDetails());

        createNewVehicleRecordSpecificationPage.enterVehicleDetails(vehicle);
        createNewVehicleRecordSpecificationPage.submitInvalidFormDetails();

        return createNewVehicleRecordSpecificationPage.isErrorMessageDisplayed(errorMsg);
    }

    public Boolean submitPageOneDetailsWithInappropriateReason (
            String reason, String prop, String errorMsg,
            CreateNewVehicleRecordIdentificationPage createNewVehicleRecordIdentificationPage) throws Exception {

        Vehicle vehicle = VehicleFactory.generateValidDetails();

        if (prop == "vin") {
            vehicle.setEmptyVinReason(reason);
        } else if (prop == "vrm") {
            vehicle.setEmptyVrmReason(reason);
        } else {
            throw new Exception("Unrecognised property. vin or vrm expected");
        }

        createNewVehicleRecordIdentificationPage.enterDetails(vehicle);
        createNewVehicleRecordIdentificationPage.submitInvalidFormDetails();
        return createNewVehicleRecordIdentificationPage.isErrorMessageDisplayed(errorMsg);
    }

    private Vehicle resetVehicleProperty(String property, Vehicle vehicle) {

        switch(property) {
            case "Country":
                vehicle.setCountryOfRegistrationId("");
                break;
            case "Registration":
                vehicle.setRegistration("");
                break;
            case "Vin":
                vehicle.setVin("");
                break;
            case "Make":
                vehicle.setMake(new Make());
                break;
            case "Date":
                vehicle.setFirstUsedDate("");
                break;
            case "Transmission":
                vehicle.setTransmissionType("");
                break;
            case "Fuel":
                vehicle.setFuelType(new FuelType().setCode("").setName(""));
                break;
            case "Model":
                vehicle.setModel(new Model());
                break;
            case "Class":
                vehicle.setVehicleClass(null);
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
