package uk.gov.dvsa.ui.pages.braketest;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.BrakeTestResultsHelper;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.module.BrakeTestResultsPageElements;

import java.util.Map;

public class BrakeTestResultsPage extends Page {
    @FindBy(id = "brake_test_results_submit") private WebElement submitButton;
    @FindBy(id = "brake_test_summary_done") private WebElement doneButton;
    @FindBy(id = "brake_test_results_cancel") private WebElement cancelButton;

    private static final String PAGE_TITLE = "Add brake test results";

    public BrakeTestResultsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public BrakeTestResultsPage completeBrakeEffortField(String outcome) {
        if(outcome.equals("pass")) {
            enterBrakeResultsPageFields(BrakeTestResultsHelper.allPass());
        } else {
            enterBrakeResultsPageFields(BrakeTestResultsHelper.allFail());
        }

        submitButton.click();

        doneButton.click();

        return this;
    }

    public BrakeTestResultsPage completeBrakeEffortGroupAFloorField() {
        enterBrakeResultsPageFields(BrakeTestResultsHelper.allPassFloorWithLocks());
        submitButton.click();
        doneButton.click();

        return this;
    }

    public BrakeTestResultsPage completeBrakeEffortGroupAPlateRollerField() {
        enterBrakeResultsPageFields(BrakeTestResultsHelper.allPassPlateRollerWithLocks());
        submitButton.click();
        doneButton.click();

        return this;
    }

    public BrakeTestResultsPage enterField(BrakeTestResultsPageElements field, String value) {
        WebElement webElement = findElement(field);
        switch (field.getFieldType()) {
            case Input:
                FormDataHelper.enterText(webElement, value);
                break;
            case Radiobutton:
            case Checkbox:
                FormDataHelper.enterInputRadioButtonOrCheckbox(webElement, Boolean.valueOf(value));
                break;
            case Dropdown:
                FormDataHelper.selectFromDropDownByVisibleText(webElement, value);
                break;
            default:
                break;
        }
        return this;
    }

    public BrakeTestResultsPage enterBrakeResultsPageFields(
            Map<BrakeTestResultsPageElements, Object> valuesMap) {
        for (Map.Entry<BrakeTestResultsPageElements, Object> entry : valuesMap.entrySet()) {
            enterField(entry.getKey(), entry.getValue().toString());
        }
        return this;
    }

    private WebElement findElement(BrakeTestResultsPageElements field) {
        return getElement(By.id(field.getId()));
    }
}
