package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.ui.Select;

import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.domain.model.vehicle.TransmissionType;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;


import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.RadioList;
import uk.gov.dvsa.ui.pages.Page;

public class CreateNewVehicleRecordIdentificationPage extends Page {

    public static final String PAGE_TITLE = "Create a new vehicle record";
    public static final String PATH = "/vehicle-step/add-step-one";

    @FindBy(tagName = "legend") private WebElement step;
    @FindBy(id = "registrationNumber") private WebElement registrationNumber;
    @FindBy(id = "VIN") private WebElement vin;
    @FindBy(id = "make") private WebElement make;
    @FindBy(id = "day") private WebElement day;
    @FindBy(id = "month") private WebElement month;
    @FindBy(id = "year") private WebElement year;
    @FindBy(id = "countryOfRegistration") private WebElement registrationCountry;
    @FindBy(id = "submit-button") private WebElement submit;
    @FindBy(id = "cancel-link") private WebElement cancel;
    @FindBy(id = "other-make-v") private WebElement otherMake;
    @FindBy(id = "emptyVrmReason") private WebElement emptyVrmReason;
    @FindBy(id = "emptyVinReason") private WebElement emptyVinReason;
    @FindBy(name = "vehicleForm[transmissionType]") private WebElement transmissionType;
    @FindBy(id = "validation-summary-id") private WebElement errorBox;

    public CreateNewVehicleRecordIdentificationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    public boolean isErrorMessageDisplayed(String errMsg) {
        if (! errorBox.isDisplayed()) {
            return false;
        }
        return errorBox.getText().toString().toLowerCase().contains(errMsg.toLowerCase());
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void selectCountryOfRegistration(Vehicle vehicle) {
        if (! vehicle.getCountryOfRegistrationId().equals("")) {
            Select selectCountryOfRegistration = new Select(registrationCountry);
            selectCountryOfRegistration.selectByIndex(1);
        }
    }

    public void setRegistrationNumber(Vehicle vehicle) {
        if (vehicle.getDvsaRegistration() != null && vehicle.getDvsaRegistration() != "") {
            registrationNumber.sendKeys(vehicle.getDvsaRegistration());
        }
    }

    public void setVin(Vehicle vehicle) {
        if (vehicle.getVin() != null && vehicle.getVin() != "") {
            this.vin.sendKeys(vehicle.getVin());
        }
    }

    public void setEmptyVinReason(String reason) {
        Select selectEmptyVinReason = new Select(emptyVinReason);
        int index = 1;

        if (reason != null && reason != "") {
            if (reason == "Not found") {
                index = 2;
            } else if (reason == "Not required") {
                index = 3;
            }
            selectEmptyVinReason.selectByIndex(index);
        }
    }

    public void setEmptyVrmReason(String reason) {
        Select selectEmptyVrmReason = new Select(emptyVrmReason);
        int index = 1;

        if (reason != null && reason != "") {
            if (reason == "Not required") {
                index = 2;
            }
            selectEmptyVrmReason.selectByIndex(index);
        }
    }


    public void selectMakeOfVehicle(Vehicle vehicle) {
        if (! vehicle.getMake().equals("")) {
            FormDataHelper.selectFromDropDownByValue(
                    make,
                    Make.findByName(vehicle.getMake()).getId().toString()
            );
        }
    }

    public void setDate(Vehicle vehicle) {
        if (! vehicle.getFirstUsedDate().equals("")) {
            String dateSplit[] = vehicle.getFirstUsedDate().split("-");
            this.year.sendKeys(dateSplit[0]);
            this.month.sendKeys(dateSplit[1]);
            this.day.sendKeys(dateSplit[2]);
        }
    }

    public void selectTransmissionType(Vehicle vehicle) {
        if (! vehicle.getTransmissionType().equals("")) {
            RadioList radioList = new RadioList(driver.findElements(By.name("vehicleForm[transmissionType]")));
            radioList.findByValue(TransmissionType.valueOf(vehicle.getTransmissionType()).getId()).click();
        }
    }

    public void enterDetails(Vehicle vehicle) {
        selectCountryOfRegistration(vehicle);
        setRegistrationNumber(vehicle);
        setEmptyVrmReason(vehicle.getEmptyVrmReason());
        vin.clear();
        setVin(vehicle);
        setEmptyVinReason(vehicle.getEmptyVinReason());
        selectMakeOfVehicle(vehicle);
        setDate(vehicle);
        selectTransmissionType(vehicle);
    }

    public CreateNewVehicleRecordSpecificationPage submit() {
        submit.click();
        return new CreateNewVehicleRecordSpecificationPage(driver);
    }

    public void submitInvalidFormDetails() {
        submit.click();
    }
}