package uk.gov.dvsa.ui.pages.vehicleinformation;

import com.thoughtworks.selenium.webdriven.commands.Click;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.domain.model.vehicle.VehicleClass;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeMotTestClassPage extends Page {

    public static final String PATH = "/change/class";
    private static final String PAGE_TITLE = "Change MOT test class";
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeMotTestClassPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeMotTestClassPage chooseClass(VehicleClass vehicleClass) {
        clickElement(By.id("class"+vehicleClass.getCode()));
        return this;
    }

    public VehicleInformationPage submit() {
        submit.click();
        return new VehicleInformationPage(driver);
    }
}
