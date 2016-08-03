package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

import static org.hamcrest.MatcherAssert.assertThat;
import static org.hamcrest.core.Is.is;

public class VehicleInformationPage extends Page {

    private static final String PAGE_TITLE = "Vehicle Details";

    @FindBy(id = "regNr") private WebElement registrationNumber;
    @FindBy (id = "vin") private WebElement vinNumber;

    public VehicleInformationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public String getRegistrationNumber() {
        return registrationNumber.getText();
    }

    public String getVinNumber() {
        return vinNumber.getText();
    }

    public void verifyVehicleRegistrationAndVin(Vehicle vehicle) {
        assertThat("The registration is as expected", getRegistrationNumber(),
                is(vehicle.getDvsaRegistration()));
        assertThat("The Vin is as expected", getVinNumber(),
                is(vehicle.getVin()));
    }
}
