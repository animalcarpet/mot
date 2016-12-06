package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.model.vehicle.CountryOfRegistration;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleCountryOfRegistrationPage extends Page {

    public static final String PATH = "/create-vehicle/country-of-registration";
    public static final String TITLE = "What is the vehicle's country of registration?";

    @FindBy(id = "countryOfRegistration") private WebElement countryOfRegistrationSelect;
    @FindBy(className = "button") private WebElement continueButton;

    public VehicleCountryOfRegistrationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), TITLE);
    }

    public VehicleFirstUseDatePage continueToVehicleFirstUseDatePage() {
        continueButton.click();
        return new VehicleFirstUseDatePage(driver);
    }

    public VehicleCountryOfRegistrationPage enterCountryOfRegistration(CountryOfRegistration countryOfRegistration) {
        FormDataHelper.selectFromDropDownByValue(countryOfRegistrationSelect, countryOfRegistration.getRegistrationCode());
        return new VehicleCountryOfRegistrationPage(driver);
    }
}
