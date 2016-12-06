package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleConfirmationPage extends Page {
    private static final String PAGE_TITLE = "MOT test started";
    public static final String PATH = "/create-vehicle/created-and-started";

    @FindBy(id = "print-inspection-sheet") private WebElement printInspectionSheetLink;

    public VehicleConfirmationPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean verifyVehicleInspectionSheet() {
        return PageInteractionHelper.isElementDisplayed(printInspectionSheetLink);
    }

    public String verifyMotTestStarted() {
        return this.getTitle();
    }
}