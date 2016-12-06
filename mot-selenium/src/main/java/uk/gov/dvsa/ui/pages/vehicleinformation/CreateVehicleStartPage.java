package uk.gov.dvsa.ui.pages.vehicleinformation;


import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class CreateVehicleStartPage extends Page {

    private static final String PAGE_TITLE = "Make a new vehicle record";
    public static final String PATH = "/create-vehicle";

    @FindBy(id = "start-create-vehicle") private WebElement startNow;

    public CreateVehicleStartPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleVrmAndVinPage continueToVinVrmPage(){
        startNow.click();
        return new VehicleVrmAndVinPage(driver);

    }

}


