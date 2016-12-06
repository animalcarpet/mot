package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleReviewPage extends Page {
    private static final String PAGE_TITLE = "Confirm new record and start test";
    public static final String PATH = "/create-vehicle/review";

    @FindBy(id = "confirmAndStartTest") private WebElement continueButton;
    @FindBy(id = "change-make-and-model") private WebElement changeMakeAndModelButton;

    public VehicleReviewPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleMakePage changeVehicleMake() {
        changeMakeAndModelButton.click();
        return new VehicleMakePage(driver);
    }

    public VehicleConfirmationPage continueToVehicleConfirmationPage() {
        continueButton.click();
        return new VehicleConfirmationPage(driver);
    }
}