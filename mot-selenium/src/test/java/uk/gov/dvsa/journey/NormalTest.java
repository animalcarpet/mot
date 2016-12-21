package uk.gov.dvsa.journey;

import uk.gov.dvsa.data.VehicleData;
import uk.gov.dvsa.domain.api.response.*;
import uk.gov.dvsa.domain.model.User;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.mot.ReasonForVehicleRefusal;
import uk.gov.dvsa.domain.model.vehicle.*;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.ui.pages.mot.*;
import uk.gov.dvsa.ui.pages.mot.modal.ManualAdvisoryModalPage;
import uk.gov.dvsa.ui.pages.vehicleinformation.*;

import java.io.IOException;
import java.net.URI;
import java.net.URISyntaxException;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.Matchers.equalToIgnoringCase;

public class NormalTest {

    private PageNavigator pageNavigator = null;
    private DefaultVehicleDataRandomizer vehicleDataRandomizer = new DefaultVehicleDataRandomizer();
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
        testResultsEntryPage.completeTestDetailsWithPassValues(false);
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

    public String startTestForVehicleUnderTest(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        return pageNavigator.goToStartTestConfirmationPage(user, vehicle).getVehicleUnderTestBanner();
    }

    public void confirmAndStartTest(User user, Vehicle vehicle) throws IOException, URISyntaxException {
        confirmationPage = pageNavigator.goToStartTestConfirmationPage(user, vehicle);
        confirmationPage.clickStartMotTest();
    }

    public void startTestConfirmationPage(User user, DvlaVehicle dvlaVehicle) throws IOException, URISyntaxException {
        confirmationPage = pageNavigator.goToStartTestConfirmationPage(user, dvlaVehicle);
    }

    public String startMotTestForDvlaVehicle(User user, DvlaVehicle dvlaVehicle) throws IOException, URISyntaxException {
        return pageNavigator.goToStartTestConfirmationPage(user, dvlaVehicle).noTestClassValidation();
    }

    public String changeColour() {
        StartTestConfirmationPage changedPage = confirmationPage.clickChangeColour().selectColour(Colours.Blue).selectSecondaryColour(Colours.Bronze).submit();
        return changedPage.getSuccessMessage();
    }

    public String changeEngine() {
        StartTestConfirmationPage changedPage = confirmationPage.clickChangeEngne().selectFuelType(FuelTypes.Diesel).fillCylinderCapacity("2200").submit();
        return changedPage.getSuccessMessage();
    }

    public String changeClass() {
        StartTestConfirmationPage changedPage = confirmationPage.clickChangeClass().chooseClass(VehicleClass.five).submit();
        return changedPage.getSuccessMessage();
    }

    public TestSummaryPage conductTrainingTest(User tester, Vehicle vehicle) throws IOException, URISyntaxException {
        TestResultsEntryGroupAPageInterface testResultsEntryPage = pageNavigator.gotoTrainingTestResultsEntryPage(tester, vehicle);
        testResultsEntryPage.completeTestDetailsWithPassValues(false);
        TestSummaryPage testSummaryPage = testResultsEntryPage.clickReviewTestButton();

        declarationSuccessful = testSummaryPage.isDeclarationDisplayed();

        return testSummaryPage;
    }

    public TestCompletePage finishTrainingTest(TestSummaryPage testSummaryPage) throws IOException, URISyntaxException {
        return testSummaryPage.clickFinishButton(TestCompletePage.class);
    }

    public void refuseToTestVehicle(User tester, Vehicle vehicle, ReasonForVehicleRefusal reason) throws IOException, URISyntaxException {
        RefuseToTestPage refuseToTestPage = pageNavigator.gotoRefuseToTestPage(tester, vehicle);
        refuseToTestPage.selectReason(reason);

        declarationSuccessful = refuseToTestPage.isDeclarationElementPresentInDom();
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

    public String getVehicleUnderTestBanner() {
        return confirmationPage.getVehicleUnderTestBanner();
    }
    public String getNoTestClassValidation() {
        return new StartTestConfirmationPage(driver).getNoTestClassValidation();
    }

    public String getRegistration() {
        return new StartTestConfirmationPage(driver).getRegistration();
    }

    public VehicleConfirmationPage createNewVehicle(User tester) throws IOException, URISyntaxException {
        CreateVehicleStartPage createVehicleStartPage =
                pageNavigator.gotoCreateNewVehicleRecordIdentificationPage(tester);

        return createVehicleStartPage
                .continueToVinVrmPage().enterRegistration(vehicleDataRandomizer.nextReg()).enterVin(vehicleDataRandomizer.nextVin())
                .continueToVehicleMakePage().selectMake(Make.FORD)
                .continueToVehicleModelPage().selectModel(Model.FORD_MONDEO)
                .continueToVehicleEnginePage().selectFuelType(FuelTypes.Petrol)
                .continueToTestClassPage().selectClass()
                .continueToVehicleColourPage().selectPrimaryColour(Colours.Blue)
                .continueToVehicleCountryOfRegistrationPage().enterCountryOfRegistration(CountryOfRegistration.Northern_Ireland)
                .continueToVehicleFirstUseDatePage().enterDate()
                .continueToVehicleReviewPage()
                .continueToVehicleConfirmationPage();
    }

    public VehicleReviewPage reviewVehcielDetails(User tester) throws IOException, URISyntaxException {
        CreateVehicleStartPage createVehicleStartPage =
                pageNavigator.gotoCreateNewVehicleRecordIdentificationPage(tester);

        return createVehicleStartPage
                .continueToVinVrmPage().enterRegistration(vehicleDataRandomizer.nextReg()).enterVin(vehicleDataRandomizer.nextVin())
                .continueToVehicleMakePage().selectMake(Make.HYUNDAI)
                .continueToVehicleModelPage().selectModel(Model.HYUNDAI_I40)
                .continueToVehicleEnginePage().selectFuelType(FuelTypes.Diesel)
                .continueToTestClassPage().selectClass()
                .continueToVehicleColourPage().selectPrimaryColour(Colours.Blue)
                .continueToVehicleCountryOfRegistrationPage().enterCountryOfRegistration(CountryOfRegistration.Northern_Ireland)
                .continueToVehicleFirstUseDatePage().enterDate()
                .continueToVehicleReviewPage();
    }

    public VehicleConfirmationPage createNewElectricVehicle(User tester) throws IOException, URISyntaxException {
        CreateVehicleStartPage createVehicleStartPage =
                pageNavigator.gotoCreateNewVehicleRecordIdentificationPage(tester);

        return createVehicleStartPage
                .continueToVinVrmPage().enterRegistration(vehicleDataRandomizer.nextReg()).enterVin(vehicleDataRandomizer.nextVin())
                .continueToVehicleMakePage().selectMake(Make.HARLEY_DAVIDSON)
                .continueToVehicleModelPage().selectModel(Model.HARLEY_DAVIDSON_FLHC)
                .continueToVehicleEnginePage().selectFuelType(FuelTypes.Electric)
                .continueToTestClassPage().selectClass()
                .continueToVehicleColourPage().selectPrimaryColour(Colours.Blue)
                .continueToVehicleCountryOfRegistrationPage().enterCountryOfRegistration(CountryOfRegistration.Northern_Ireland)
                .continueToVehicleFirstUseDatePage().enterDate()
                .continueToVehicleReviewPage()
                .continueToVehicleConfirmationPage();
    }
}
