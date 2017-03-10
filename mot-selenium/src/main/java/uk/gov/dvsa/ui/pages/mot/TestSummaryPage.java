package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
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
    private static final String VALID_PIN = "123456";

    @FindBy(id = "confirm_test_result") private WebElement finishTestButton;
    @FindBy(id = "oneTimePassword") private WebElement pinInputField;
    @FindBy(id = "testStatus") private WebElement testStatus;
    @FindBy(id = "start_inspection_button") private WebElement startReinspectionButton;
    @FindBy(id = "motTestType") private WebElement testTypePrompt;
    @FindBy(id = "cancel_test_result") private WebElement backToResultsEntryLink;
    @FindBy(id = "siteidentry") private WebElement siteIdInput;
    @FindBy(css = ".validation-summary li") private WebElement validationMessage;
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

    public TestSummaryPage fillSiteIdInput(String siteId) {
        FormDataHelper.enterText(siteIdInput, siteId);
        return this;
    }

    public <T extends Page>T clickFinishButton(Class<T> clazz) {
        if (isOneTimePasswordBoxDisplayed()) {
            pinInputField.sendKeys(VALID_PIN);
        }

        finishTestButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public TestResultsEntryPageInterface clickBackToResultsEntryLink() {
        backToResultsEntryLink.click();
        return new TestResultsEntryNewPage(driver);
    }

    public void clickStartReinspectionButton() {
        startReinspectionButton.click();
    }

    public String getDeclarationText() {
        return driver.findElement(declarationElement).getText();
    }

    public boolean isDeclarationTextDisplayed() {
        return PageInteractionHelper.isElementDisplayed(driver.findElement(declarationElement));
    }

    public boolean isDeclarationDisplayed() {
        return PageInteractionHelper.isElementDisplayed(declarationElement);
    }

    public boolean isOneTimePasswordBoxDisplayed() {
        return PageInteractionHelper.isElementDisplayed(pinInputField);
    }

    public boolean isExpiryDateDisplayed() {
        return PageInteractionHelper.isElementDisplayed(driver.findElement(expiryDate));
    }

    public boolean isDefectDisplayed(String defectName) {
        return PageInteractionHelper.isElementDisplayed(By.xpath(String.format(defect, defectName)));
    }

    public boolean isValidationMessageDisplayed() {
        return PageInteractionHelper.isElementDisplayed(validationMessage);
    }
}

