package uk.gov.dvsa.ui.pages.mot.retest;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ReTestSummaryPage extends Page {

    private static final String PAGE_TITLE = "MOT re-test summary";
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
    @FindBy(id = "confirm_test_result") private WebElement finishAndPrintButton;
    @FindBy(id = "start_inspection_button") private WebElement startReinspectionButton;
    @FindBy(id = "motTestType") private WebElement testTypePrompt;
    @FindBy(id = "declarationStatement") private WebElement declarationElement;


    public ReTestSummaryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_REINSPECTION);
    }

    public ReTestCompletePage finishTestAndPrint(){
        pinInputField.sendKeys("123456");
        finishTestButton.click();

        return new ReTestCompletePage(driver);
    }

    public ReTestSummaryPage fillSiteIdInput(String siteId) {
        siteIdInput().sendKeys(siteId);

        return this;
    }

    public void clickFinishButton() {
        finishAndPrintButton.click();
    }

    public void clickStartReinspectionButton() {
        startReinspectionButton.click();
    }

    public ReTestSummaryPage selectTestType(String testType) {
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
}
