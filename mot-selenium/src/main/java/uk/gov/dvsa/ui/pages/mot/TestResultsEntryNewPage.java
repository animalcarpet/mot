package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.ui.Select;
import uk.gov.dvsa.domain.model.mot.BrakeTestType;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.domain.model.mot.OdometerUnit;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.PageLocator;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestConfigurationPage;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestResultsPage;
import uk.gov.dvsa.ui.pages.mot.retest.ReTestSummaryPage;

public class TestResultsEntryNewPage extends AbstractReasonsForRejectionPage implements
    TestResultsEntryPageInterface, TestResultsEntryGroupAPageInterface {

    public static final String PATH = "/mot-test";

    private static final String PAGE_TITLE = "MOT test results";

    @FindBy(id = "vehicleMakeModel") private WebElement vehicleMakeModel;
    @FindBy(id = "vehicleRegistration") private WebElement vehicleRegistration;
    @FindBy(id = "firstUsedDate") private WebElement firstUsedDate;
    @FindBy(id = "odometerReading") private WebElement odometerReading;
    @FindBy(id = "odometerNotice") private WebElement odometerNotice;
    @FindBy(id = "odometer") private WebElement odometerField;
    @FindBy(id = "addDefectButton") private WebElement addDefect;
    @FindBy(id = "searchForDefect") private WebElement searchForDefect;
    @FindBy(id = "addBrakeTestButton") private WebElement addBrakeTest;
    @FindBy(id = "reviewTest") private WebElement reviewTest;
    @FindBy(id = "cancelTest") private WebElement cancelTest;
    @FindBy(id = "reviewTest") private WebElement reviewTestButton;
    @FindBy(id = "addOdometerReadingButton") private WebElement addOdometerReading;
    @FindBy(id = "odometer_submit") private WebElement odometerSubmit;
    @FindBy(id = "unit") private WebElement odometerUnit;
    @FindBy(xpath = "//*[@id='rfrList']//a[contains(., 'Edit')]") private WebElement editDefectLink;
    @FindBy(id = "brakeTestResultsNotice") private WebElement brakeTestResultsNotice;

    public TestResultsEntryNewPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getVehicleMakeModel() {
        return vehicleMakeModel.getText();
    }

    public String getVehicleRegistration() {
        return vehicleRegistration.getText();
    }

    public String getVehicleFirstUsedDate() {
        return firstUsedDate.getText();
    }

    public String getOdometerReading() {
        return odometerReading.getText();
    }

    public boolean addOdomoterReadingButtonIsDisplayed() {
        return addOdometerReading.isDisplayed();
    }

    public boolean addDefectButtonIsDisplayed() {
        return addDefect.isDisplayed();
    }

    public OdometerReadingPage clickAddReadingButton() {
        addOdometerReading.click();
        return new OdometerReadingPage(driver);
    }

    public TestSummaryPage clickReviewTestButton() {
        return clickReviewTestButton(false);
    }

    public <T extends Page> T clickReviewTestButton(boolean isRetest) {
        reviewTest.click();

        if (isRetest) {
            return (T) new ReTestSummaryPage(driver);
        }
        return (T) new TestSummaryPage(driver);
    }

    public boolean searchForDefectIsDisplayed() {
        return searchForDefect.isDisplayed();
    }

    public boolean addBrakeTestButtonIsDisplayed() {
        return addBrakeTest.isDisplayed();
    }

    public boolean reviewTestButtonIsDisplayed() {
        return reviewTest.isDisplayed();
    }

    public TestAbortedPage abortMotTest(CancelTestReason reason){
        processTestCancellation(reason);

        return new TestAbortedPage(driver);
    }

    public TestAbandonedPage abandonMotTest(CancelTestReason reason){
        processTestCancellation(reason);

        return new TestAbandonedPage(driver);
    }

    public TestResultsEntryNewPage completeBrakeTestWithPassValues() {
        addDefaultBrakeTestValues("pass");

        return this;
    }

    public DefectCategoriesPage clickAddDefectButton() {
        addDefect.click();

        return new DefectCategoriesPage(driver);
    }

    public EditDefectPage navigateToEditDefectPage(Defect defect) {
        editDefectLink.click();

        return new EditDefectPage(driver, defect.getAddOrRemovalType());
    }

    public boolean isOdometerReadingUpdateSuccessMessageDisplayed(){
        return validationMessage.getText().equals("The odometer reading has been updated");
    }

    public RemoveDefectPage navigateToRemoveDefectPage(Defect defect) {
        removeDefectLink.click();

        return new RemoveDefectPage(driver, defect.getAddOrRemovalType());
    }

    public boolean isDefectRemovedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been removed:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }

    public boolean isDefectEditedSuccessMessageDisplayed(Defect defect) {
        return validationMessage.getText().equals(
                String.format("This %s has been edited:\n%s", defect.getAddOrRemovalType(), defect.getAddOrRemoveName()));
    }

    public boolean isDefectInReasonsForRejection(Defect defect) {
        return reasonForRejectionTitle.isDisplayed() && reasonForRejectionTitle.getText().contains(defect.getDefectName());
    }

    public Boolean isClickReviewTestButtonPresent(){
        return reviewTestButton.isDisplayed();
    }

    public TestResultsEntryNewPage addOdometerReading(int odometerReading) {
        addOdometerReading.click();
        FormDataHelper.enterText(odometerField, String.valueOf(odometerReading));
        setOdometerUnit(OdometerUnit.KILOMETRES);
        odometerSubmit.click();

        return this;
    }

    public boolean isPassNoticeDisplayed(){
        return brakeTestResultsNotice.getText().contains("Pass");
    }

    public TestResultsEntryPageInterface completeTestDetailsWithPassValues() {
        addOdometerReading(1001);
        completeBrakeTestWithPassValues();

        return this;
    }

    public SearchForADefectPage clickSearchForADefectButton() {
        searchForDefect.click();
        return new SearchForADefectPage(driver);
    }

    public BrakeTestResultsPage completeTestWithFloorBrakeTestsWithLockBoxes(){
        addOdometerReading(10000);
        return addFloorBrakeTestsWithLockBoxes();
    }

    public BrakeTestResultsPage addFloorBrakeTestsWithLockBoxes() {
        BrakeTestConfigurationPage brakeTestConfigurationPage = clickAddBrakeTest();

        BrakeTestResultsPage brakeTestResultsPage =
            brakeTestConfigurationPage.fillAllFieldsWithValidDataForGroupAAndSubmit(BrakeTestType.FLOOR);

        return brakeTestResultsPage.completeBrakeEffortGroupAFloorField();
    }

    public BrakeTestResultsPage completeTestWithRollerBrakeTestsWithLockBoxes() {
        addOdometerReading(10000);
        return addRollerBrakeTestWithLockBoxes();
    }

    public BrakeTestResultsPage addRollerBrakeTestWithLockBoxes(){
        BrakeTestConfigurationPage brakeTestConfigurationPage = clickAddBrakeTest();

        BrakeTestResultsPage brakeTestResultsPage =
            brakeTestConfigurationPage.fillAllFieldsWithValidDataForGroupAAndSubmit(BrakeTestType.ROLLER);

        return brakeTestResultsPage.completeBrakeEffortGroupAPlateRollerField();
    }

    public TestResultsEntryNewPage clickRepaired(String defect, Class clazz) {
        repairDefect(defect, clazz);
        return this;
    }

    private void setOdometerUnit(OdometerUnit unit) {
        new Select(odometerUnit).selectByValue(unit.getValue());
    }

    private BrakeTestConfigurationPage clickAddBrakeTest() {
        addBrakeTest.click();
        return PageLocator.getBrakeTestConfigurationPage(driver);
    }

    private void processTestCancellation(CancelTestReason reason) {
        cancelTest.click();

        ReasonToCancelTestPage cancelTestPage = new ReasonToCancelTestPage(driver);
        cancelTestPage.enterReason(reason);
        cancelTestPage.clickConfirmAndCancelTest();
    }

    public ReasonToCancelTestPage clickCancelTest(){
        cancelTest.click();
        return new ReasonToCancelTestPage(driver);
    }

    private TestResultsEntryNewPage addDefaultBrakeTestValues(String outcome) {
        addBrakeTest.click();

        BrakeTestConfigurationPage brakeTestConfigurationPage =
                PageLocator.getBrakeTestConfigurationPage(driver);

        BrakeTestResultsPage brakeTestResultsPage =
                brakeTestConfigurationPage.fillAllFieldsWithValidDataAndSubmit();

        brakeTestResultsPage.completeBrakeEffortField(outcome);

        return this;
    }

    public String getOdometerNoticeText() {
        return odometerNotice.getText();
    }
}