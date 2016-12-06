package uk.gov.dvsa.ui.pages.vehicleinformation;


import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleVrmAndVinPage extends Page {
    private static final String PAGE_TITLE = "What are the vehicle's registration mark and VIN?";
    public static final String PATH = "/create-vehicle/vrm-and-vin";

    @FindBy(id = "reg-input") private WebElement registrationInputField;
    @FindBy(id = "vin-input") private WebElement vinInputField;
    @FindBy(id = "continueButton") private WebElement continueButton;

    public VehicleVrmAndVinPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleMakePage continueToVehicleMakePage() {
        continueButton.click();
        return new VehicleMakePage(driver);
    }

    public VehicleVrmAndVinPage enterRegistration(String registration) {
        FormDataHelper.enterText(registrationInputField, registration);
        return this;
    }

    public VehicleVrmAndVinPage enterVin(String vin) {
        FormDataHelper.enterText(vinInputField, vin);
        return this;
    }
}
