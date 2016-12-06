package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleEnginePage extends Page {
    private static final String PAGE_TITLE = "Engine and fuel type";
    public static final String PATH = "/create-vehicle/engine";

    @FindBy(id = "fuel-type") private WebElement fuelTypeDropdown;
    @FindBy(id = "cylinder-capacity-input") private WebElement cylinderCapacityTextField;
    @FindBy(className = "button") private WebElement continueButton;

    public VehicleEnginePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleEnginePage selectFuelType(FuelTypes fuelTypes) {
        FormDataHelper.selectFromDropDownByValue(fuelTypeDropdown, fuelTypes.getCode());
        if(PageInteractionHelper.isElementDisplayed(cylinderCapacityTextField)) {
            FormDataHelper.enterText(cylinderCapacityTextField, "1900");
        }

        return this;
    }

    public VehicleTestClassPage continueToTestClassPage() {
        continueButton.click();
        return new VehicleTestClassPage(driver);
    }
}
