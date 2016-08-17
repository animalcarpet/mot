package uk.gov.dvsa.ui.pages;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;

public abstract class AbstractVehicleSearchPage extends Page {

    private static final String PAGE_TITLE = "Find a vehicle";
    private static final String EXPECTED_BREADCRUMB = "MOT testing";

    @FindBy(name = "registration") protected WebElement registrationField;
    @FindBy(name = "vin") protected WebElement vinField;
    @FindBy(id = "global-breadcrumb") private WebElement globalBreadcrumb;
    @FindBy(id = "cancel_vehicle_search") private WebElement cancelButton;
    @FindBy(id = "vehicle-search") private WebElement searchButton;

    public AbstractVehicleSearchPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean doesCancelAndReturnButtonExist() {
        return cancelButton.isDisplayed();
    }

    public boolean doesGlobalBreadcrumbExist() {
        return globalBreadcrumb.getText().contains(EXPECTED_BREADCRUMB);
    }

    public VehicleSearchResultsPage searchVehicle(Vehicle vehicle){
        return (VehicleSearchResultsPage) searchVehicle(vehicle, true);
    }

    public AbstractVehicleSearchResultsPage searchVehicle(Vehicle vehicle, boolean isDataValid){
        String regNumber = vehicle.getDvsaRegistration() == null ?
                vehicle.getDvlaRegistration() : vehicle.getDvsaRegistration();

        return searchVehicle(regNumber, vehicle.getVin(), isDataValid);
    }

    public AbstractVehicleSearchResultsPage searchVehicle(String registration, String vin, boolean isDataValid) {
        FormDataHelper.enterText(registrationField, registration);
        FormDataHelper.enterText(vinField, vin);
        searchButton.click();
        if (isDataValid) {
            return new VehicleSearchResultsPage(driver);
        }
        return new VehicleSearchNoResultsPage(driver);
    }

    public boolean isSearchSectionDisplayed() {
        return searchButton.isDisplayed() && vinField.isDisplayed() && registrationField.isDisplayed();
    }

    public boolean isBasePageContentCorrect() {
        return this.doesGlobalBreadcrumbExist() && this.doesCancelAndReturnButtonExist();
    }
}
