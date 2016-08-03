package uk.gov.dvsa.ui.pages.mot;

import org.joda.time.DateTime;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.VehicleSearchPage;

import java.util.List;

import static uk.gov.dvsa.helper.FormDataHelper.enterText;
import static uk.gov.dvsa.helper.FormDataHelper.selectFromDropDownByVisibleText;

public class ContingencyTestEntryPage extends Page {

    public static final String PATH = "/contingency";
    private static final String PAGE_TITLE = "Record contingency test";

    @FindBy(id = "contingency_code") private WebElement contingencyCodeInput;
    @FindBy(id = "radio-test-type-group-labelnormal-test") private WebElement contingencyTestTypeNormalTest;
    @FindBy(id = "contingency-reason__communication-problem") private WebElement reasonForContingencyTestCommunicationProblems;
    @FindBy(id = "confirm_ct_button") private WebElement confirmContingencyTestDetailsButton;
    @FindBy(id = "contingency_time-hour") private WebElement timeHour;
    @FindBy(id = "contingency_time-minutes") private WebElement timeMinutes;
    @FindBy(id = "contingency_time-ampm") private WebElement dropDownAmPm;
    @FindBy(id = "contingency_date-day") private WebElement dateTestDay;
    @FindBy(id = "contingency_date-month") private WebElement dateTestMonth;
    @FindBy(id = "contingency_date-year") private WebElement dateTestYear;

    public ContingencyTestEntryPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    private ContingencyTestEntryPage fillContingencyCode(String code) {
        contingencyCodeInput.sendKeys(code);

        return this;
    }

    private ContingencyTestEntryPage selectContingencyTestTypeNormalTest() {
        contingencyTestTypeNormalTest.click();

        return this;
    }

    private ContingencyTestEntryPage selectReasonForContingencyTestCommunicationProblems() {
        reasonForContingencyTestCommunicationProblems.click();

        return this;
    }

    public ContingencyTestEntryPage clickConfirmContingencyTestDetailsButton() {
        confirmContingencyTestDetailsButton.click();

        return this;
    }

    private ContingencyTestEntryPage fillDateContingencyTestPerformed(DateTime date) {
        dateTestDay.sendKeys(date.dayOfMonth().getAsShortText());
        dateTestMonth.sendKeys(String.valueOf(date.getMonthOfYear()));
        dateTestYear.sendKeys(date.year().getAsShortText());
        return this;
    }

    public VehicleSearchPage fillContingencyTestFormAndConfirm(String code, DateTime date) {
        fillContingencyCode(code)
                .selectReasonForContingencyTestCommunicationProblems()
                .fillDateContingencyTestPerformed(date)
                .fillTimeOfTest(date)
                .clickConfirmContingencyTestDetailsButton();

        return new VehicleSearchPage(driver);
    }

    private ContingencyTestEntryPage fillTimeOfTest(DateTime date) {
        enterText(timeHour, date.toString("hh"));
        enterText(timeMinutes, String.valueOf(date.toString("mm")));
        selectFromDropDownByVisibleText(dropDownAmPm, date.toString("a").toLowerCase());

        return this;
    }

    public String getValidationText(String element){
        String elementId = String.format("%s__validation-message", element);
        return driver.findElement(By.id(elementId)).getText();
    }

    public List<WebElement> getValidationList(){
        return driver.findElements(By.cssSelector("ol#validation_summary_list li"));
    }
}
