package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeEnginePage extends Page {

    public static final String PATH = "/change/engine";
    private static final String PAGE_TITLE = "Change engine specification";

    @FindBy(id = "fuel-type") WebElement fuelTypeDropdown;
    @FindBy(id = "cylinder-capacity") WebElement cylinderCapacityField;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeEnginePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeEnginePage fillCylinderCapacity(String cylinderCapacity) {
        FormDataHelper.enterText(cylinderCapacityField, cylinderCapacity);
        return this;
    }

    public ChangeEnginePage selectFuelType(FuelTypes fuelType) {
        FormDataHelper.selectFromDropDownByValue(fuelTypeDropdown, fuelType.getCode());
        return this;
    }

    public VehicleInformationPage submit() {
        submit.click();
        return new VehicleInformationPage(driver);
    }
}
