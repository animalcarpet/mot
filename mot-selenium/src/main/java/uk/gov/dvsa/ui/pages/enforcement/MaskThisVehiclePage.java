package uk.gov.dvsa.ui.pages.enforcement;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.vehicleinformation.VehicleInformationPage;

public class MaskThisVehiclePage extends Page {

    private static final String PAGE_TITLE = "Mask this vehicle";
    private static final String CONFIRMATION_BANNER_TITLE = "Vehicle has been masked successfully";

    @FindBy(id = "mask-vehicle") private WebElement maskThisVehicleButton;
    @FindBy(linkText = "Cancel and return to vehicle record") private WebElement cancelAndReturnLink;

    public MaskThisVehiclePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public MaskUnmaskConfirmationPage clickMaskThisVehicleButton() {
        maskThisVehicleButton.click();
        return new MaskUnmaskConfirmationPage(driver, CONFIRMATION_BANNER_TITLE);
    }

    public VehicleInformationPage clickCancelAndReturnLink() {
        cancelAndReturnLink.click();
        return new VehicleInformationPage(driver);
    }
}
