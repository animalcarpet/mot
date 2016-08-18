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
    private static final String PAGE_TITLE_REINSPECTION = "MOT testing\n" +
            "MOT reinspection summary";
    private static final By SITE_ID_INPUT_LOCATOR = By.id("siteidentry");
    private static final String FAIL_MSG = "Fail";

    private WebElement siteIdInput() {
        return driver.findElement(SITE_ID_INPUT_LOCATOR);
    }

    @FindBy(id = "confirm_test_result") private WebElement finishTestButton;
    @FindBy(id = "oneTimePassword") private WebElement pinInputField;
    @FindBy(id = "testStatus") private WebElement testStatus;
    @FindBy(id = "start_inspection_button") private WebElement startReinspectionButton;
    @FindBy(id = "motTestType") private WebElement testTypePrompt;
    @FindBy(id = "declarationStatement") private WebElement declarationElement;
    @FindBy(id = "cancel_test_result") private WebElement backToResultsEntryLink;
    private By siteIdTextBox = By.id("siteidentry");
    private By expiryDate = By.id("expiryDate");

    public TestSummaryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_REINSPECTION);
    }

    public TestCompletePage finishTestAndPrint(){
        pinInputField.sendKeys("123456");

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

    public boolean isFailedNoticeDisplayed() {
        return testStatus.getText().contains(FAIL_MSG);
    }

    public boolean isDeclarationTextDisplayed() {
        return declarationElement.isDisplayed();
    }

    public String getDeclarationText() {
        return declarationElement.getText();
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

    public TestResultsEntryPageInterface clickBackToResultsEntryLink() {
        backToResultsEntryLink.click();
        if(ConfigHelper.isTestResultEntryImprovementsEnabled()) {
            return new TestResultsEntryNewPage(driver);
        }
        return new TestResultsEntryPage(driver);
    }
}

