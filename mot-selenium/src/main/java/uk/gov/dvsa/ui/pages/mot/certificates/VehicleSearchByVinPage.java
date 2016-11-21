package uk.gov.dvsa.ui.pages.mot.certificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class VehicleSearchByVinPage extends VehicleSearchPage {

    public static final String PATH = "/replacement-certificate-vehicle-search-vin";
    @FindBy (id = "vin") private WebElement textField;

    public VehicleSearchByVinPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public ReplacementCertificateResultsPage searchVehicle(Vehicle vehicle) {
        FormDataHelper.enterText(textField, vehicle.getVin());
        return search();
    }
}
