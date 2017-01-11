package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.CacheLookup;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.ui.Select;
import uk.gov.dvsa.domain.model.vehicle.Vehicle;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class StartTestConfirmationPage extends Page {
    public final String path = "/start-test-confirmation/";
    private final String PAGE_TITLE = "MOT testing";
    private static final String PAGE_TITLE_TRAINING = "Training test\n" +
            "Confirm vehicle for test";

    @FindBy(id = "confirm_vehicle_confirmation") private WebElement confirmButton;
    @FindBy(id = "retest_vehicle_confirmation") private WebElement retestvehicleconfirmation;
    @FindBy(id = "fuel-type-select") private WebElement fuelType;
    @FindBy(id = "vehicle-class-select") private WebElement vehicleClass;
    @FindBy(id = "primary-colour") @CacheLookup private WebElement primaryColor;
    @FindBy(id = "secondary-colour") private WebElement secondaryColour;
    @FindBy(id = "refuse-to-test") private WebElement refuseToTestVehicle;

    public StartTestConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE, PAGE_TITLE_TRAINING);
    }

    public TestOptionsPage clickStartMotTest() {
        confirmButton.click();
        return new TestOptionsPage(driver);
    }

    public TestResultsEntryPage clickStartMotTestWhenConductingContingencyTest() {
        confirmButton.click();

        return new TestResultsEntryPage(driver);
    }

    public String getConfirmVehicleAndStartRetestButtonText(){
        return retestvehicleconfirmation.getText();
    }

    public VehicleDetailsChangedPage changeVehicleDetailAndSubmit(Vehicle vehicle) {
        Select s = new Select(this.primaryColor);
        s.selectByValue(vehicle.getSecondaryColour());
        confirmButton.click();
        return new VehicleDetailsChangedPage(driver);
    }

    public RefuseToTestPage refuseToTestVehicle() {
        refuseToTestVehicle.click();
        return new RefuseToTestPage(driver);
    }

    public boolean isTesterOnReTestConfirmationPage() {
        return getTitle().contentEquals("Confirm vehicle for retest");
    }
}
