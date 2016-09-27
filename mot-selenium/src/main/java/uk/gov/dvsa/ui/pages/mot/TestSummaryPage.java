package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.ConfigHelper;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class TestSummaryPage extends Page {

    public static String PATH = "/mot-test/%s/test-summary";
    private static final String PAGE_TITLE = "MOT test summary";
    private static final String PAGE_TITLE_REINSPECTION = "MOT testing\nMOT reinspection summary";
    private static final String PAGE_TITLE_RE_TEST = "MOT testing\nMOT re-test summary";
    private static final By SITE_ID_INPUT_LOCATOR = By.id("siteidentry");
    private static final String FAIL_MSG = "Fail";
    private static final String VALID_PIN = "123456";

    @FindBy(id = "confirm_test_result") private WebElement finishTestButton;
    @FindBy(id = "oneTimePassword") private WebElement pinInputField;
    @FindBy(id = "testStatus") private WebElement testStatus;
    @FindBy(id = "start_inspection_button") private WebElement startReinspectionButton;
    @FindBy(id = "motTestType") private WebElement testTypePrompt;
    @FindBy(id = "cancel_test_result") private WebElement backToResultsEntryLink;
    private By siteIdTextBox = By.id("siteidentry");
    private By expiryDate = By.id("expiryDate");
    private By declarationElement = By.id("declarationStatement");
    private String defect = "//*[@class='col-lg-12']//li[contains(text(),'%s')]";


    public TestSummaryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_REINSPECTION, PAGE_TITLE_RE_TEST);
    }

    public TestCompletePage finishTest(){
        if (isOneTimePasswordBoxDisplayed()) {
            pinInputField.sendKeys(VALID_PIN);
        }

        return clickFinishButton();
    }

    public TestSummaryPage fillSiteIdInput(String siteId) {
        siteIdInput().sendKeys(siteId);
        return this;
    }

    public TestCompletePage clickFinishButton() {
        finishTestButton.click();
        return new TestCompletePage(driver);
    }

    public void clickStartReinspectionButton() {
        startReinspectionButton.click();
    }

    public TestSummaryPage selectTestType(String testType) {
        FormDataHelper.selectFromDropDownByValue(testTypePrompt, testType);
        return this;
    }

    public boolean isDeclarationTextDisplayed() {
        return PageInteractionHelper.isElementDisplayed(driver.findElement(declarationElement));
    }

    public String getDeclarationText() {
        return driver.findElement(declarationElement).getText();
    }

    public boolean isDeclarationDisplayed() {
        return PageInteractionHelper.isElementDisplayed(declarationElement);
    }

    public boolean isOneTimePasswordBoxDisplayed() {
        return PageInteractionHelper.isElementDisplayed(pinInputField);
    }

    public boolean isDeclarationElementPresentInDom() throws NoSuchElementException {
        try {
            driver.findElement(By.id("declarationElement"));
            return true;
        }
        catch (TimeoutException exception){
            return false;
        }
    }

    public TestSummaryPage enterSiteId(String siteId) {
        FormDataHelper.enterText(driver.findElement(siteIdTextBox), siteId);
        return this;
    }

    public boolean isExpiryDateDisplayed() {
        return PageInteractionHelper.isElementDisplayed(driver.findElement(expiryDate));
    }

    public boolean isDefectDisplayed(String defectName) {
        return PageInteractionHelper.isElementDisplayed(By.xpath(String.format(defect, defectName)));
    }

    public TestResultsEntryPageInterface clickBackToResultsEntryLink() {
        backToResultsEntryLink.click();
        if(ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            return new TestResultsEntryNewPage(driver);
        }
        return new TestResultsEntryPage(driver);
    }

    private WebElement siteIdInput() {
        return driver.findElement(SITE_ID_INPUT_LOCATOR);
    }
}

