package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Colours;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeColourPage extends Page {

    public static final String PATH = "/change/colour";
    private static final String PAGE_TITLE = "Change colour";

    @FindBy(id = "colour") WebElement colourDropdown;
    @FindBy(id = "secondaryColour") WebElement secondaryColourDropdown;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeColourPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeColourPage selectColour(Colours colour) {
        FormDataHelper.selectFromDropDownByValue(colourDropdown, colour.getCode());
        return this;
    }

    public ChangeColourPage selectSecondaryColour(Colours colour) {
        FormDataHelper.selectFromDropDownByValue(secondaryColourDropdown, colour.getCode());
        return this;
    }

    public VehicleInformationPage submit() {
        submit.click();
        return new VehicleInformationPage(driver);
    }
}
