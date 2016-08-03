package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.ui.Select;
import uk.gov.dvsa.domain.model.mot.CancelTestReason;
import uk.gov.dvsa.domain.model.mot.OdometerUnit;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.Configurator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.PageLocator;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestConfigurationPage;
import uk.gov.dvsa.ui.pages.braketest.BrakeTestResultsPage;

public class OdometerReadingPage extends Page {

    private static final String PAGE_TITLE = "Odometer Reading";

    @FindBy(id = "odometer") private WebElement odometerReadingTextBox;
    @FindBy(id = "unit") private WebElement odometerUnitDropdown;
    @FindBy(id = "odometer_submit") private WebElement odometerSubmitButton;
    @FindBy(id = "validation-message--failure") private WebElement validationMessageError;

    public OdometerReadingPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public <T extends Page> T addOdometerReading(int odometerReading, OdometerUnit odometerUnit, boolean isValid) {
        odometerReadingTextBox.sendKeys(String.valueOf(odometerReading));
        setOdometerUnit(odometerUnit.getValue());
        odometerSubmitButton.click();
        if (isValid) {
            return (T)MotPageFactory.newPage(driver, TestResultsEntryNewPage.class);
        }
        return (T) this;
    }

    private void setOdometerUnit(String unit) {
        if (unit.equals("mi") || unit.equals("km")) {
            setUnit(unit);
            return;
        }
        throw new RuntimeException("Wrong odometer unit provided!");
    }

    private void setUnit(String unit) {
        new Select(odometerUnitDropdown).selectByValue(unit);
    }

    public boolean isOdometerReadingUpdateErrorMessageDisplayed(){
        return validationMessageError.getText().equals("The odometer reading should be a valid number between 0 and 999,999");
    }
}
