package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.Keys;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.domain.navigation.PageNavigator;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleMakePage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's make?";
    public static final String PATH = "/create-vehicle/make";

    @FindBy(id = "vehicleMake") private WebElement vehicleMakeDropdown;
    @FindBy(className = "button") private WebElement continueButton;

    public VehicleMakePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleMakePage selectMake(Make make){
        FormDataHelper.selectFromDropDownByValue(vehicleMakeDropdown, make.getId().toString());
        vehicleMakeDropdown.sendKeys(Keys.TAB);
        return this;
    }

    public VehicleModelPage continueToVehicleModelPage() {
        continueButton.click();
        return new VehicleModelPage(driver);
    }

    public VehicleModelPage updateVehicleMake(Make make) {
        FormDataHelper.selectFromDropDownByValue(vehicleMakeDropdown, make.getId().toString());
        vehicleMakeDropdown.sendKeys(Keys.TAB);
        return continueToVehicleModelPage();
    }
}
