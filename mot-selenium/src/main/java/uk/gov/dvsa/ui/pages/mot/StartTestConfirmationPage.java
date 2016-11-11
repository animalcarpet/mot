package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.CacheLookup;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.ui.Select;
import uk.gov.dvsa.domain.api.response.Vehicle;
import uk.gov.dvsa.domain.model.vehicle.Colours;
import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class StartTestConfirmationPage extends Page {
    public static final String path = "/start-test-confirmation/";
    private final String PAGE_TITLE = "MOT testing";
    private static final String PAGE_TITLE_TRAINING = "Training test\n" +
            "Confirm vehicle for test";
    private static final String PAGE_TITLE_NON_MOT = "Non-MOT test\n" +
            "Confirm vehicle for test";

    @FindBy(id = "confirm_vehicle_confirmation") private WebElement confirmButton;
    @FindBy(id = "retest_vehicle_confirmation") private WebElement retestvehicleconfirmation;
    @FindBy(id = "vehicleWeight") private WebElement vehicleWeight;
    @FindBy(id = "vehicle-class-select") private WebElement classDropdown;
    @FindBy(id = "fuel-type-select") private WebElement fuelType;
    @FindBy(id = "vehicle-class-select") private WebElement vehicleClass;
    @FindBy(id = "primary-colour") @CacheLookup private WebElement primaryColor;
    @FindBy(id = "secondary-colour") private WebElement secondaryColour;
    @FindBy(id = "refuse-to-test") private WebElement refuseToTestVehicle;

    private By vinLocator = By.id("vehicleVINnumber");
    private By registrationLocator = By.id("vehicleRegistrationNumber");

    public StartTestConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_TRAINING, PAGE_TITLE_NON_MOT);
    }

    public TestOptionsPage clickStartMotTest() {
        confirmButton.click();
        return new TestOptionsPage(driver);
    }

    public <T extends Page> T clickStartMotTest(Class<T> clazz) {
        confirmButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public <T extends Page> T clickStartMotTestWhenConductingContingencyTest(Class<T> clazz) {
        confirmButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public VehicleDetailsChangedPage changeVehicleDetailAndSubmit(Vehicle vehicle) {
        Select s = new Select(this.primaryColor);
        s.selectByValue(
                Colours.findByName(vehicle.getColourSecondary().getName()).getId().toString()
        );
        confirmButton.click();
        return new VehicleDetailsChangedPage(driver);
    }

    public RefuseToTestPage refuseToTestVehicle() {
        refuseToTestVehicle.click();
        return new RefuseToTestPage(driver);
    }

    public StartTestConfirmationPage selectClass(String classNumber){
        FormDataHelper.selectFromDropDownByVisibleText(classDropdown, classNumber);
        return this;
    }

    public String getVehicleWeight() {
        return vehicleWeight.getText();
    }

    public String getVin() {
        return driver.findElement(vinLocator).getText();
    }

    public String getRegistration() {
        return driver.findElement(registrationLocator).getText();
    }
}
