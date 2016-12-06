package uk.gov.dvsa.ui.pages.vehicleinformation;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleClassPage extends Page {

    public static final String PATH = "/create-vehicle/class";

    public VehicleClassPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return true;
    }

    public VehicleColourPage continueToVehicleColourPage() {
        driver.navigateToPath(VehicleColourPage.PATH);
        return new VehicleColourPage(driver);
    }
}
