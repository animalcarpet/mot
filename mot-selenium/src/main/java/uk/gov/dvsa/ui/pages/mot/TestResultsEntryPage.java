package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.ui.Select;
import uk.gov.dvsa.domain.model.mot.BrakeTestType;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.OdometerUnit;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.PageLocator;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestConfigurationPage;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestResultsPage;

public class TestResultsEntryPage extends Page implements
    TestResultsEntryPageInterface, TestResultsEntryGroupAPageInterface {
    private static final String PAGE_TITLE = "MOT test results entry";
    private static final String PAGE_TITLE_TRAINING = "Training test\n" + "MOT test started";

    @FindBy(id = "odometer_submit") private WebElement odometerSubmit;
    @FindBy(id = "createCertificate") private WebElement reviewTestButton;
    @FindBy (id = "cancelMotTest") private WebElement cancelMotTestLink;
    @FindBy(className = "active") private WebElement stepInfo;
    @FindBy(tagName = "h1") private WebElement stepTitle;
    @FindBy(className = "col-md-8") private WebElement carNameAndYear;
    @FindBy(id = "vehicle-summary-more") private WebElement carYear;
    @FindBy(id = "toggle-details") private WebElement toggleDetails;
    @FindBy(name = "unit") private WebElement odometerUnit;
    @FindBy(id = "odometer") private WebElement odometerField;
    @FindBy(id = "addOdometer") private WebElement editOdometerButton;
    @FindBy(id = "info-message") private WebElement infoMessage;
    @FindBy(id = "add_rfr_button") private WebElement AddRFRButton;
    @FindBy(id = "reasonsForRejection") private WebElement reasonsForRejection;
    @FindBy(id = "failureCount") private WebElement numberOfFailures;
    @FindBy(id = "failureResults") private WebElement failureResults;
    @FindBy(id = "prsCount") private WebElement numberOfPRS;
    @FindBy(id = "prsResults") private WebElement prsResults;
    @FindBy(id = "advisoryCount") private WebElement numberOfAdvisories;
    @FindBy(id = "advisoryResults") private WebElement advisoryResults;
    @FindBy(id = "odometerReading") private WebElement odometerReading;
    @FindBy(id = "addBrakeTestResults") private WebElement addBrakeTest;
    @FindBy(id = "brakeTestResult") private WebElement brakeTestResult;
    @FindBy(id = "viewBrakeTestResults") private WebElement viewBrakeTestResults;
    @FindBy(id = "odometerReadingNotice") private WebElement odometerReadingNotice;
    @FindBy(id = "brakeTestResultsNotice") private WebElement brakeTestResultsNotice;
    @FindBy(id = "reason-for-rejection-modal") private WebElement viewReasonsForRejectionsLink;
    @FindBy(id = "quit") private WebElement motDone;
    @FindBy(id = "rfr-modal-close") private WebElement closeRFRList;
    @FindBy(id = "rfr-remove") private WebElement removeRfR;
    @FindBy(id = "vehicle-summary-more") private WebElement vehicleSummaryMore;
    @FindBy(linkText = "Edit") private WebElement editButton;
    @FindBy(id = "vtsNameAndAddress") private WebElement inspectionLocation;

    public TestResultsEntryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_TRAINING);
    }

    public TestResultsEntryPageInterface completeTestDetailsWithPassValues(boolean isRetest) {
        addOdometerReading(10000);
        addDefaultBrakeTestValues("pass");

        return this;
    }

    public TestResultsEntryPage completeTestDetailsWithFailValues() {
        addOdometerReading(9999999);
        addDefaultBrakeTestValues("fail");

        return this;
    }

    private TestResultsEntryPage addDefaultBrakeTestValues(String outcome) {
        BrakeTestConfigurationPage brakeTestConfigurationPage = clickAddBrakeTest();

        BrakeTestResultsPage brakeTestResultsPage =
                brakeTestConfigurationPage.fillAllFieldsWithValidDataAndSubmit();

        brakeTestResultsPage.completeBrakeEffortField(outcome);

        return this;
    }

    public BrakeTestResultsPage completeTestWithFloorBrakeTestsWithLockBoxes() {
        addOdometerReading(10000);
        return addFloorBrakeTestsWithLockBoxes();
    }

    public BrakeTestResultsPage addFloorBrakeTestsWithLockBoxes() {
        BrakeTestConfigurationPage brakeTestConfigurationPage = clickAddBrakeTest();

        BrakeTestResultsPage brakeTestResultsPage =
            brakeTestConfigurationPage.fillAllFieldsWithValidDataForGroupAAndSubmit(BrakeTestType.FLOOR);

        brakeTestResultsPage.completeBrakeEffortGroupAFloorField();
        return brakeTestResultsPage;
    }

    public BrakeTestResultsPage completeTestWithRollerBrakeTestsWithLockBoxes() {
        addOdometerReading(10000);
        return addRollerBrakeTestsWithLockBoxes();
    }

    public BrakeTestResultsPage addRollerBrakeTestsWithLockBoxes(){
        BrakeTestConfigurationPage brakeTestConfigurationPage = clickAddBrakeTest();

        BrakeTestResultsPage brakeTestResultsPage =
            brakeTestConfigurationPage.fillAllFieldsWithValidDataForGroupAAndSubmit(BrakeTestType.ROLLER);

        brakeTestResultsPage.completeBrakeEffortGroupAPlateRollerField();
        return brakeTestResultsPage;
    }

    private BrakeTestConfigurationPage clickAddBrakeTest() {
        addBrakeTest.click();
        return PageLocator.getBrakeTestConfigurationPage(driver);
    }

    public TestItemSelector clickAddFRFButton() {
        AddRFRButton.click();
        return new TestItemSelector(driver);
    }

    public TestSummaryPage clickReviewTestButton(){
        reviewTestButton.click();

        return new TestSummaryPage(driver);
    }

    public void clickReviewTest() {
        reviewTestButton.click();
    }

    public TestResultsEntryPage addOdometerReading(int odometerReading) {
        editOdometerButton.click();
        odometerField.sendKeys(String.valueOf(odometerReading));

        setOdometerUnit(OdometerUnit.KILOMETRES.getValue());
        odometerSubmit.click();

        return this;
    }

    private void setOdometerUnit(String unit) {
        if (unit.equals("mi") || unit.equals("km")) {
            setUnit(unit);
            return;
        }
        throw new RuntimeException("Wrong Odometer unit provided!");
    }

    private void setUnit(String unit) {
        new Select(odometerUnit).selectByValue(unit);
    }

    public boolean isFailedNoticeDisplayed(){
        return brakeTestResultsNotice.getText().contains("Failed");
    }

    public boolean isPassNoticeDisplayed(){
        return brakeTestResultsNotice.getText().contains("Pass");
    }

    public TestAbandonedPage abandonMotTest(CancelTestReason reason){
        processTestCancellation(reason);

        return new TestAbandonedPage(driver);
    }

    public TestAbandonedPage abandonMotTest2FaActiveUser(CancelTestReason reason){
        processTestCancellation2faActiveUser(reason);

        return new TestAbandonedPage(driver);
    }

    public TestAbortedPage abortMotTest(CancelTestReason reason){
        processTestCancellation(reason);

        return new TestAbortedPage(driver);
    }

    private void processTestCancellation(CancelTestReason reason) {
        cancelMotTestLink.click();

        ReasonToCancelTestPage cancelTestPage = new ReasonToCancelTestPage(driver);
        cancelTestPage.enterReason(reason);
        cancelTestPage.clickConfirmAndCancelTest();
    }

    private void processTestCancellation2faActiveUser(CancelTestReason reason) {
        cancelMotTestLink.click();

        ReasonToCancelTestPage cancelTestPage = new ReasonToCancelTestPage(driver);
        cancelTestPage.enterReason2FaActiveUser(reason);
        cancelTestPage.clickConfirmAndCancelTest();
    }

    public ReasonToCancelTestPage clickCancelTest(){
        cancelMotTestLink.click();
        return new ReasonToCancelTestPage(driver);
    }

    public TestResultsEntryPage fillOdometerReadingAndSubmit(int odometerReading) {
        editOdometerButton.click();
        PageInteractionHelper.waitForElementToBeVisible(odometerSubmit, Configurator.defaultFastWebElementTimeout);
        odometerField.sendKeys(String.valueOf(odometerReading));

        setOdometerUnit(OdometerUnit.KILOMETRES.getValue());
        odometerSubmit.click();

        return this;
    }

    public Boolean isClickReviewTestButtonPresent(){
        return reviewTestButton.isDisplayed();
    }
}
