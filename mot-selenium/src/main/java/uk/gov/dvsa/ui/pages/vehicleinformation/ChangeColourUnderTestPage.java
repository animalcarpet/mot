package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Colours;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;

public class ChangeColourUnderTestPage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's colour?";
    @FindBy(id = "colour") WebElement colourDropdown;
    @FindBy(id = "secondaryColour") WebElement secondaryColourDropdown;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeColourUnderTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeColourUnderTestPage selectColour(Colours colour) {
        FormDataHelper.selectFromDropDownByValue(colourDropdown, colour.getCode());
        return this;
    }

    public ChangeColourUnderTestPage selectSecondaryColour(Colours colour) {
        FormDataHelper.selectFromDropDownByValue(secondaryColourDropdown, colour.getCode());
        return this;
    }

    public StartTestConfirmationPage submit() {
        submit.click();
        return new StartTestConfirmationPage(driver);
    }
}