package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class VehicleFirstUseDatePage extends Page{
    private static final String PAGE_TITLE = "What is the vehicle's date of first use?";
    public static final String PATH = "/create-vehicle/first-use-date";

    @FindBy(id = "dateDay") private WebElement dayTextField;
    @FindBy(id = "dateMonth") private WebElement monthTextField;
    @FindBy(id = "dateYear") private WebElement yearTextField;
    @FindBy(className = "button") private WebElement continueButton;

    public VehicleFirstUseDatePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleFirstUseDatePage enterDate() {
        FormDataHelper.enterText(dayTextField, "23");
        FormDataHelper.enterText(monthTextField, "11");
        FormDataHelper.enterText(yearTextField, "2016");

        return this;
    }

    public VehicleReviewPage continueToVehicleReviewPage() {
        continueButton.click();
        return new VehicleReviewPage(driver);
    }
}