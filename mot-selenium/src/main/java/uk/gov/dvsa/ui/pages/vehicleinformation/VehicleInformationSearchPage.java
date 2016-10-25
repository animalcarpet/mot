package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleInformationSearchPage extends Page {

    public static final String PATH = "/vehicle/search";

    private static final String PAGE_TITLE = "Search for vehicle information by...";

    @FindBy (id = "vehicle-search") private WebElement vehicleSearchInput;
    @FindBy (id = "item-selector-btn-search") private WebElement searchButton;

    public VehicleInformationSearchPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public <T extends Page>T searchVehicleByRegistration(String registration, Class<T> clazz) {
        FormDataHelper.enterText(vehicleSearchInput, registration);
        searchButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public VehicleInformationPage findVehicleAndRedirectToVehicleInformationPage(String registration) {
        FormDataHelper.enterText(vehicleSearchInput, registration);
        searchButton.click();
        return new VehicleInformationPage(driver);
    }
}