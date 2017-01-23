package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.FuelTypes;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;

public class ChangeEngineUnderTestPage extends Page {

    private static final String PAGE_TITLE = "Engine and fuel type";

    @FindBy(id = "fuel-type") WebElement fuelTypeDropdown;
    @FindBy(id = "cylinder-capacity") WebElement cylinderCapacityField;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeEngineUnderTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeEngineUnderTestPage fillCylinderCapacity(String cylinderCapacity) {
        FormDataHelper.enterText(cylinderCapacityField, cylinderCapacity);
        return this;
    }

    public ChangeEngineUnderTestPage selectFuelType(FuelTypes fuelType) {
        FormDataHelper.selectFromDropDownByValue(fuelTypeDropdown, fuelType.getCode());
        return this;
    }

    public StartTestConfirmationPage submit() {
        submit.click();
        return new StartTestConfirmationPage(driver);
    }
}