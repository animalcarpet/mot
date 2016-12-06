package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleTestClassPage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's test class?";
    public static final String PATH = "/create-vehicle/class";

    @FindBy(id = "testClass3") private WebElement testClass3;
    @FindBy(id = "continueButton") private WebElement continueButton;

    public VehicleTestClassPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleTestClassPage selectClass() {
        testClass3.click();
        return this;
    }

    public VehicleColourPage continueToVehicleColourPage() {
        continueButton.click();
        return new VehicleColourPage(driver);
    }
}
