package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Colours;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleColourPage extends Page {
    private static final String PAGE_TITLE = "What is the vehicle's colour?";
    public static final String PATH = "/create-vehicle/colour";

    @FindBy(id = "primaryColour") private WebElement primaryColourDropdown;
    @FindBy(id = "secondaryColours") private WebElement secondaryColourDropdown;
    @FindBy(id = "continueButton") private WebElement continueButton;

    public VehicleColourPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public VehicleColourPage selectPrimaryColour(Colours colour){
        FormDataHelper.selectFromDropDownByValue(primaryColourDropdown, colour.getCode());
        return this;
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleCountryOfRegistrationPage continueToVehicleCountryOfRegistrationPage() {
        continueButton.click();
        return new VehicleCountryOfRegistrationPage(driver);
    }
}
