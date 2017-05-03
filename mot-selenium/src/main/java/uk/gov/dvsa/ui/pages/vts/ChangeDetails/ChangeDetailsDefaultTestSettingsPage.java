package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.helper.enums.BrakeTestConstants;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vts.VehicleTestingStationPage;

public class ChangeDetailsDefaultTestSettingsPage extends Page {

    public static final String PATH = "/vehicle-testing-station/%s/configure-brake-test-defaults";
    private static final String PAGE_TITLE = "Change default test settings";

    @FindBy(id = "ROLLR-default-brake-test-class-1-and-2-label") private WebElement rollerBrakeClass1And2RadioButton;
    @FindBy(id = "PLATE-default-brake-test-class-1-and-2-label") private WebElement plateBrakeClass1And2RadioButton;
    @FindBy(id = "DECEL-default-brake-test-class-1-and-2-label") private WebElement decelBrakeClass1And2RadioButton;
    @FindBy(id = "FLOOR-default-brake-test-class-1-and-2-label") private WebElement floorBrakeClass1And2RadioButton;
    @FindBy(id = "GRADT-default-brake-test-class-1-and-2-label") private WebElement gradtBrakeClass1And2RadioButton;
    @FindBy(id = "ROLLR-default-service-brake-test-class-3-and-above-label") private WebElement rollerServiceBrakeClass3AndAboveRadioButton;
    @FindBy(id = "PLATE-default-service-brake-test-class-3-and-above-label") private WebElement plateServiceBrakeClass3AndAboveRadioButton;
    @FindBy(id = "DECEL-default-service-brake-test-class-3-and-above-label") private WebElement decelServiceBrakeClass3AndAboveRadioButton;
    @FindBy(id = "ROLLR-default-parking-brake-test-class-3-and-above-label") private WebElement rollerParkingBrakeClass3AndAboveRadioButton;
    @FindBy(id = "PLATE-default-parking-brake-test-class-3-and-above-label") private WebElement plateParkingBrakeClass3AndAboveRadioButton;
    @FindBy(id = "DECEL-default-parking-brake-test-class-3-and-above-label") private WebElement decelParkingBrakeClass3AndAboveRadioButton;
    @FindBy(id = "GRADT-default-parking-brake-test-class-3-and-above-label") private WebElement gradtParkingBrakeClass3AndAboveRadioButton;
    @FindBy(id = "save") private WebElement saveTestDefaultsButton;

    public ChangeDetailsDefaultTestSettingsPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeDetailsDefaultTestSettingsPage changeDefaultTestSettingsClass1And2
            (BrakeTestConstants.BrakeTestType brakeTestType) {
        switch (brakeTestType) {
            case Roller: FormDataHelper.enterInputRadioButtonOrCheckbox(rollerBrakeClass1And2RadioButton, true);
                break;
            case Plate: FormDataHelper.enterInputRadioButtonOrCheckbox(plateBrakeClass1And2RadioButton, true);
                break;
            case Decelerometer: FormDataHelper.enterInputRadioButtonOrCheckbox(decelBrakeClass1And2RadioButton, true);
                break;
            case Floor: FormDataHelper.enterInputRadioButtonOrCheckbox(floorBrakeClass1And2RadioButton, true);
                break;
            case Gradient: FormDataHelper.enterInputRadioButtonOrCheckbox(gradtBrakeClass1And2RadioButton, true);
                break;
        }
        return this;
    }

    public ChangeDetailsDefaultTestSettingsPage changeDefaultTestSettingsClass3AndAbove
            (BrakeTestConstants.BrakeTestType serviceBrakeTestType, BrakeTestConstants.BrakeTestType parkingBrakeTestType) {
        switch (serviceBrakeTestType) {
            case Roller: FormDataHelper.enterInputRadioButtonOrCheckbox(rollerServiceBrakeClass3AndAboveRadioButton, true);
                break;
            case Plate: FormDataHelper.enterInputRadioButtonOrCheckbox(plateServiceBrakeClass3AndAboveRadioButton, true);
                break;
            case Decelerometer: FormDataHelper.enterInputRadioButtonOrCheckbox(decelServiceBrakeClass3AndAboveRadioButton, true);
                break;
        }
        switch (parkingBrakeTestType) {
            case Roller: FormDataHelper.enterInputRadioButtonOrCheckbox(rollerParkingBrakeClass3AndAboveRadioButton, true);
                break;
            case Plate: FormDataHelper.enterInputRadioButtonOrCheckbox(plateParkingBrakeClass3AndAboveRadioButton, true);
                break;
            case Decelerometer: FormDataHelper.enterInputRadioButtonOrCheckbox(decelParkingBrakeClass3AndAboveRadioButton, true);
                break;
            case Gradient: FormDataHelper.enterInputRadioButtonOrCheckbox(gradtParkingBrakeClass3AndAboveRadioButton, true);
            break;
        }
        return this;
    }

    public VehicleTestingStationPage clickSaveTestDefaultsButton() {
        saveTestDefaultsButton.click();
        return new VehicleTestingStationPage(driver);
    }
}
