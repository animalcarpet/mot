package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.PageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class TestCompareResultsPage extends Page {

    private static final String PAGE_TITLE = "Differences found between tests";
    private static final String SERVICE_SCORE_LOCATOR = "(.//table/tbody/tr[2]//select)[1]";
    private static final String PARKING_SCORE_LOCATOR = "(.//table/tbody/tr[4]//select)[1]";
    private static final String SERVICE_CATEGORY_LOCATOR = "(.//table/tbody/tr[2]//select)[3]";
    private static final String PARKING_CATEGORY_LOCATOR = "(.//table/tbody/tr[4]//select)[3]";
    private static final String SERVICE_JUSTIFICATION_INPUT_LOCATOR = ".//table/tbody/tr[2]//textarea";
    private static final String PARKING_JUSTIFICATION_INPUT_LOCATOR = ".//table/tbody/tr[4]//textarea";
    private static final String JUSTIFICATION_VALUE = "Justification comment";
    private static final String FINAL_JUSTIFICATION_VALUE = "Final justification comment";

    @FindBy (id = "finalJustification") private WebElement finalJustification;

    @FindBy (id = "record_assessment_button") private WebElement recordAssessmentButton;

    private WebElement serviceScore() {
        return driver.findElement(By.xpath(SERVICE_SCORE_LOCATOR));
    }

    private WebElement serviceCategory() {
        return driver.findElement(By.xpath(SERVICE_CATEGORY_LOCATOR));
    }

    private WebElement parkingScore() {
        return driver.findElement(By.xpath(PARKING_SCORE_LOCATOR));
    }

    private WebElement parkingCategory() {
        return driver.findElement(By.xpath(PARKING_CATEGORY_LOCATOR));
    }

    private WebElement serviceJustificationInput() {
        return driver.findElement(By.xpath(SERVICE_JUSTIFICATION_INPUT_LOCATOR));
    }

    private WebElement parkingJustificationInput() {
        return driver.findElement(By.xpath(PARKING_JUSTIFICATION_INPUT_LOCATOR));
    }

    private MotAppDriver driver;

    public TestCompareResultsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
        this.driver = driver;
        PageFactory.initElements(driver, this);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public TestCompareResultsPage selectServiceScore(String scoreValue) {
        FormDataHelper.selectFromDropDownByValue(serviceScore(), scoreValue);

        return this;
    }

    public TestCompareResultsPage selectServiceCategory(String categoryValue) {
        FormDataHelper.selectFromDropDownByValue(serviceCategory(), categoryValue);

        return this;
    }

    public TestCompareResultsPage selectParkingScore(String scoreValue) {
        FormDataHelper.selectFromDropDownByValue(parkingScore(), scoreValue);

        return this;
    }

    public TestCompareResultsPage selectParkingCategory(String categoryValue) {
        FormDataHelper.selectFromDropDownByValue(parkingCategory(), categoryValue);

        return this;
    }

    public TestCompareResultsPage fillFinalJustificationInputWithDefaultValue() {
        finalJustification.sendKeys(FINAL_JUSTIFICATION_VALUE);

        return this;
    }

    public TestCompareResultsPage fillFinalJustificationInput(String value) {
        finalJustification.sendKeys(value);

        return this;
    }

    public TestCompareResultsPage fillServiceJustificationInputWithDefaultValue() {
        serviceJustificationInput().sendKeys(JUSTIFICATION_VALUE);

        return this;
    }

    public TestCompareResultsPage fillParkingJustificationInputWithDefaultValue() {
        parkingJustificationInput().sendKeys(JUSTIFICATION_VALUE);

        return this;
    }

    public void clickRecordAssessmentButton() {
        recordAssessmentButton.click();
    }
}
