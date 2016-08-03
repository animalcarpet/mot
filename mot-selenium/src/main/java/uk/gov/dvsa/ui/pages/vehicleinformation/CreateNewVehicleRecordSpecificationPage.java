package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Colour;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class CreateNewVehicleRecordSpecificationPage extends Page {

    private static final String PAGE_TITLE = "Create a new vehicle record";
    public static final String PATH = "/vehicle-step/add-step-two";

    @FindBy(tagName = "legend") private WebElement step;
    @FindBy(id = "model") private WebElement model;
    @FindBy(id = "fuelType") private WebElement fuelType;
    @FindBy(id = "vehicleClass") private WebElement vehicleClass;
    @FindBy(id = "cylinderCapacity") private WebElement cylinderCapacity;
    @FindBy(id = "colour") private WebElement colour;
    @FindBy(id = "secondaryColour") private WebElement secondaryColour;
    @FindBy(id = "submit-button") private WebElement submitButton;
    @FindBy(id = "back-link") private WebElement backLink;
    @FindBy(id = "modelOther") private WebElement modelOther;
    @FindBy(id = "validation-summary-id") private WebElement errorBox;

    public CreateNewVehicleRecordSpecificationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }


    public boolean isErrorMessageDisplayed(String errMsg){
        if(! errorBox.isDisplayed()){
            return false;
        }
        return errorBox.getText().toString().toLowerCase().contains(errMsg.toLowerCase());
    }

    public void enterVehicleDetails(Vehicle vehicle) {

        if(! vehicle.getModel().equals("")){
            FormDataHelper.selectFromDropDownByValue(
                    model,
                    Model.findByName(vehicle.getModel()).getId().toString()
            );
        }

        if(! vehicle.getVehicleClass().equals("")){
            FormDataHelper.selectFromDropDownByValue(
                    vehicleClass,
                    vehicle.getVehicleClass()
            );
        }

        if(! vehicle.getFuelType().equals("")){
            FormDataHelper.selectFromDropDownByValue(
                    fuelType,
                    FuelTypes.findByName(vehicle.getFuelType()).getId().toString()
            );

            FormDataHelper.enterText(
                    cylinderCapacity,
                    vehicle.getCylinderCapacity()
            );
        }



        if(! vehicle.getColour().equals("")){
            FormDataHelper.selectFromDropDownByValue(
                    colour, Colour.findByName(vehicle.getColour()).getId().toString()
            );
        }

        if(! vehicle.getColourSecondary().equals("")){
            FormDataHelper.selectFromDropDownByValue(
                    secondaryColour,
                    Colour.findByName(vehicle.getColourSecondary()).getId().toString()
            );
        }
    }

    public void submitInvalidFormDetails() {
        submitButton.click();
    }

    public CreateNewVehicleRecordConfirmPage submit() {
        submitButton.click();
        return new CreateNewVehicleRecordConfirmPage(driver);
    }
}