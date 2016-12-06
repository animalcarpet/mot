package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleModelPage extends Page {
    private static final String PAGE_TITLE = "What is the vehicle's model?";
    public static final String PATH = "/create-vehicle/model";

    @FindBy(id = "vehicleModel") private WebElement vehicleModelDropdown;
    @FindBy(className = "button") private WebElement continueButton;

    public VehicleModelPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleModelPage selectModel(Model model){
        FormDataHelper.selectFromDropDownByValue(vehicleModelDropdown, model.getId().toString());
        return this;
    }

    public VehicleEnginePage continueToVehicleEnginePage() {
        PageInteractionHelper.waitForElementToBeClickable(continueButton);
        continueButton.click();
        return new VehicleEnginePage(driver);
    }

    public VehicleReviewPage continueToVehicleReviewPage() {
        PageInteractionHelper.waitForElementToBeClickable(continueButton);
        continueButton.click();
        return new VehicleReviewPage(driver);
    }

    public VehicleReviewPage updateVehicleModel(Model model) {
        FormDataHelper.selectFromDropDownByValue(vehicleModelDropdown, model.getId().toString());
        return continueToVehicleReviewPage();
    }
}
