package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.By;
import org.openqa.selenium.NoSuchElementException;
import org.openqa.selenium.TimeoutException;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.ReasonForVehicleRefusal;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class RefuseToTestPage extends Page {

    @FindBy(id = "refuse-mot-test") private WebElement refuseMotTest;
    @FindBy(id = "validation-summary-id") private WebElement validationSummary;
    @FindBy(id = "back_to_search") private WebElement backToSearch;
    @FindBy(id = "registration-mark") private WebElement registrationMark;
    @FindBy(id = "vin") private WebElement vin;
    @FindBy(id = "make-and-model") private WebElement makeAndModel;
    @FindBy(id = "refusal-1") private WebElement reasonUnabletoIdentifyDateOfFirstUse;
    @FindBy(id = "refusal-2") private WebElement reasonVehicleTooDirtyToExamine;
    @FindBy(id = "refusal-3") private WebElement reasonVehicleIsNotFitToBeDriven;
    @FindBy(id = "refusal-4") private WebElement reasonInsecurityOfLoad;
    @FindBy(id = "refusal-5") private WebElement reasonVehicleConfigSizeUnsuitable;
    @FindBy(id = "refusal-6") private WebElement reasonVehicleEmitsSubstantialSmoke;
    @FindBy(id = "refusal-7") private WebElement reasonUnableToOpenDevice;
    @FindBy(id = "refusal-8") private WebElement reasonInspectionMayBeDangerous;
    @FindBy(id = "refusal-9") private WebElement reasonRequestedTestFeeNotPaid;
    @FindBy(id = "refusal-10") private WebElement reasonSuspectMaintenanceHistoryOfDieselEngine;
    @FindBy(id = "refusal-11") private WebElement reasonMotorcycleFrameStampedNotForRoad;
    @FindBy(id = "refusal-26") private WebElement reasonVtsNotAuthorisedToTestVehicleClass;

    private static final String PAGE_TITLE = "Refuse to test";
    public static final String PATH = "/refuse-to-test/%s";

    public RefuseToTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public void selectReason(ReasonForVehicleRefusal reason) {
        switch (reason) {
            case UNABLE_IDENTIFY_DATE_FIRST_USE:
                selectUnableToIdentifyDateOfFirstUse();
                break;
            case VEHICLE_TOO_DIRTY_TO_EXAMINE:
                selectVehicleTooDirtyToExamine();
                break;
            case VEHICLE_IS_NOT_FIT_TO_BE_DRIVEN:
                selectVehicleIsNotFitToBeDriven();
                break;
            case INSECURITY_OF_LOAD:
                selectInsecurityOfLoad();
                break;
            case VEHICLE_CONFIG_SIZE_UNSUITABLE:
                selectVehicleConfigSizeUnsuitable();
                break;
            case VEHICLE_EMITS_SUBSTANTIAL_SMOKE:
                selectVehicleEmitsSubstantialSmoke();
                break;
            case UNABLE_TO_OPEN_DEVICE:
                selectUnableToOpenDevice();
                break;
            case INSPECTION_MAY_BE_DANGEROUS:
                selectInspectionMayBeDangerous();
                break;
            case REQUESTED_TEST_FEE_NOT_PAID:
                selectRequestedTestFeeNotPaid();
                break;
            case SUSPECT_MAINTENANCE_HISTORY_OF_DIESEL_ENGINE:
                selectSuspectMaintenanceHistoryOfDieselEngine();
                break;
            case MOTORCYCLE_FRAME_STAMPED_NOT_FOR_ROAD:
                selectMotorcycleFrameStampedNotForRoad();
                break;
            case VTS_NOT_AUTHORISED_TO_TEST_VEHICLE_CLASS:
                selectVtsNotAuthorisedToTestVehicleClass();
                break;
            default:
                break;
        }
    }

    private void selectUnableToIdentifyDateOfFirstUse() {
        reasonUnabletoIdentifyDateOfFirstUse.click();
    }

    private void selectVehicleTooDirtyToExamine() {
        reasonVehicleTooDirtyToExamine.click();
    }

    private void selectVehicleIsNotFitToBeDriven() {
        reasonVehicleIsNotFitToBeDriven.click();
    }

    private void selectInsecurityOfLoad() {
        reasonInsecurityOfLoad.click();
    }

    private void selectVehicleConfigSizeUnsuitable() {
        reasonVehicleConfigSizeUnsuitable.click();
    }

    private void selectVehicleEmitsSubstantialSmoke() {
        reasonVehicleEmitsSubstantialSmoke.click();
    }

    private void selectUnableToOpenDevice() {
        reasonUnableToOpenDevice.click();
    }

    private void selectInspectionMayBeDangerous() {
        reasonInspectionMayBeDangerous.click();
    }

    private void selectRequestedTestFeeNotPaid() {
        reasonRequestedTestFeeNotPaid.click();
    }

    private void selectSuspectMaintenanceHistoryOfDieselEngine() {
        reasonSuspectMaintenanceHistoryOfDieselEngine.click();
    }

    private void selectMotorcycleFrameStampedNotForRoad() {
        reasonMotorcycleFrameStampedNotForRoad.click();
    }

    private void selectVtsNotAuthorisedToTestVehicleClass() {
        reasonVtsNotAuthorisedToTestVehicleClass.click();
    }

    public String getVin() {
        return vin.getText();
    }

    public boolean isErrorMessageDisplayed() {
        return validationSummary.isDisplayed();
    }

    public boolean isDeclarationElementPresentInDom(){
        try {
            driver.findElement(By.id("declarationElement"));
            return true;
        }
        catch (TimeoutException e){
            return false;
        }
    }
}