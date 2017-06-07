package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.domain.navigation.MotPageFactory;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.VehicleTestingAdvicePage;
import uk.gov.dvsa.ui.pages.vehicleinformation.*;

public class StartTestConfirmationPage extends Page {
    public static final String path = "/start-test-confirmation/";
    private final String PAGE_TITLE = "MOT testing";
    private static final String PAGE_TITLE_TRAINING = "Training test\n" +
            "Confirm vehicle and start test";
    private static final String PAGE_TITLE_NON_MOT = "Non-MOT test\n" +
            "Confirm vehicle and start test";
    private final String BANNER_PAGE_TITLE = "This vehicle is currently under test";

    @FindBy(id = "confirm_vehicle_confirmation") private WebElement confirmButton;
    @FindBy(id = "retest_vehicle_confirmation") private WebElement retestVehicleConfirmation;
    @FindBy(id = "vehicleWeight") private WebElement vehicleWeight;
    @FindBy(id = "change-vehicle-class") private WebElement changeClassButton;
    @FindBy(id = "change-vehicle-colour") private WebElement changeColourButton;
    @FindBy(id = "change-vehicle-engine") private WebElement changeEngineButton;
    @FindBy(id = "change-vehicle-make") private WebElement changeMakeAndModelButton;
    @FindBy(id = "change-vehicle-country-of-registration") private WebElement changeCountryOfRegistrationButton;
    @FindBy(id = "fuel-type-select") private WebElement fuelType;
    @FindBy(id = "refuse-to-test") private WebElement refuseToTestVehicle;
    @FindBy(id = "motExpiryDate") private WebElement motExpireDate;
    @FindBy(className = "banner--error") private WebElement vehicleUnderTestBanner;
    @FindBy(className = "heading-medium") private WebElement noTestClassValidation;
    @FindBy(id = "validation-message--success") private WebElement changeDetailsSuccessMessage;
    @FindBy(id = "vehicle-testing-advice") private WebElement vehicleTestingAdviceUrl;
    @FindBy(id = "not-authorised-to-test-vehicle-class") private WebElement notAuthorisedToTestVehicleClass;

    private By vinLocator = By.id("vehicleVINnumber");
    private By registrationLocator = By.id("vehicleRegistrationNumber");

    public StartTestConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        if (PageInteractionHelper.isElementDisplayed(noTestClassValidation)) {
            return noTestClassValidation.getText().contains(BANNER_PAGE_TITLE);
        } else {
            return PageInteractionHelper.verifyTitle(getTitle(),PAGE_TITLE, PAGE_TITLE_TRAINING, PAGE_TITLE_TRAINING, PAGE_TITLE_NON_MOT);
        }
    }

    public TestOptionsPage clickStartMotTest() {
        confirmButton.click();
        return new TestOptionsPage(driver);
    }

    public <T extends Page> T clickStartMotTestWhenConductingContingencyTest(Class<T> clazz) {
        confirmButton.click();
        return MotPageFactory.newPage(driver, clazz);
    }

    public RefuseToTestPage refuseToTestVehicle() {
        refuseToTestVehicle.click();
        return new RefuseToTestPage(driver);
    }

    public ChangeClassUnderTestPage clickChangeClass(){
        changeClassButton.click();
        return new ChangeClassUnderTestPage(driver);
    }

    public ChangeColourUnderTestPage clickChangeColour(){
        changeColourButton.click();
        return new ChangeColourUnderTestPage(driver);
    }

    public ChangeEngineUnderTestPage clickChangeEngine(){
        changeEngineButton.click();
        return new ChangeEngineUnderTestPage(driver);
    }

    public ChangeMakeUnderTestPage clickChangeMakeAndModel(){
        changeMakeAndModelButton.click();
        return new ChangeMakeUnderTestPage (driver);
    }

    public ChangeCountryOfRegistrationUnderTestPage clickChangeCountryOfRegistration(){
        changeCountryOfRegistrationButton.click();
        return new ChangeCountryOfRegistrationUnderTestPage (driver);
    }

    public VehicleTestingAdvicePage clickVehicleTestingAdviceUrl(){
        vehicleTestingAdviceUrl.click();
        return new VehicleTestingAdvicePage (driver);
    }

    public String getVehicleWeight() {
        return vehicleWeight.getText();
    }

    public String getNoTestClassValidation() {
        return noTestClassValidation.getText();
    }
    public String noTestClassValidation() {
         confirmButton.click();
        return getNoTestClassValidation();
    }

    public String getVehicleUnderTestBanner() {
        return vehicleUnderTestBanner.getText();
    }

    public String getVin() {
        return driver.findElement(vinLocator).getText();
    }

    public String getRegistration() {
        return driver.findElement(registrationLocator).getText();
    }

    public String getSuccessMessage() {
        return changeDetailsSuccessMessage.getText();
    }

    public String getNotAuthorisedToTestVehicleClassText() {

        return notAuthorisedToTestVehicleClass.getText();
    }
}
