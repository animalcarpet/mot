package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class EnforcementTestSummaryPage extends Page {
    private static final String PAGE_TITLE = "MOT test summary";
    public static final String PATH = "/enforcement/mot-test/%s/test-summary?type=testNumber";

    @FindBy(id = "testStatus") private WebElement testStatus;
    @FindBy(id = "expiryDate") private WebElement expiryDate;
    @FindBy(id = "motTestNumber") private WebElement motTestNumber;
    @FindBy (id = "abort_test_button") private WebElement abortButton;
    private By startReInspectionButton = By.id("start_inspection_button");
    private By motTestTypeDropdown = By.id("motTestType");
    private By complaintRefTextBox = By.id("complaintRef");


    public EnforcementTestSummaryPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public String getTestStatus() {
        return testStatus.getText();
    }

    public String getExpiryDate() {
        return expiryDate.getText();
    }

    public String getMotTestNumber() {
        return motTestNumber.getText();
    }

    public <T extends Page> T abort(Class<T> clazz) {
        abortButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public TestResultsEntryReInspectionPage startReInspection() {
        driver.findElement(startReInspectionButton).click();
        return new TestResultsEntryReInspectionPage(driver);
    }

    public boolean printCertificateButtonExists(String testNumber) {
        WebElement printButton = driver.findElement
                (By.cssSelector(String.format("a[href*='%s']", testNumber)));

        return printButton.isDisplayed();
    }

    public EnforcementTestSummaryPage selectInspectionType(String inspectionType) {
        FormDataHelper.selectFromDropDownByVisibleText(driver.findElement(motTestTypeDropdown), inspectionType);
        return this;
    }

    public EnforcementTestSummaryPage enterComplaintReference(String reference) {
        if(PageInteractionHelper.isElementDisplayed(driver.findElement(complaintRefTextBox))){
            FormDataHelper.enterText(driver.findElement(complaintRefTextBox), reference);
        }
        return this;
    }
}
