package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;

public class ChangeClassUnderTestPage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's test class?";
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeClassUnderTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeClassUnderTestPage chooseClass(VehicleClass vehicleClass) {
        clickElement(By.id("class" + vehicleClass.getCode()));
        return this;
    }

    public StartTestConfirmationPage submit() {
        submit.click();
        return new StartTestConfirmationPage(driver);
    }
}