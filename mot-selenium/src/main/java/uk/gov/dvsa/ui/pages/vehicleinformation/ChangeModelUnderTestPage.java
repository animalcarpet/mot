package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.Keys;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;
import uk.gov.dvsa.ui.pages.mot.StartTestConfirmationPage;

public class ChangeModelUnderTestPage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's model?";

    @FindBy(id = "vehicleModel") WebElement vehicleModelDropdown;
    @FindBy(id = "otherModel") WebElement vehicleModelOtherField;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeModelUnderTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeModelUnderTestPage selectModel(Model model) {
        FormDataHelper.selectFromDropDownByValue(vehicleModelDropdown, model.getId().toString());
        vehicleModelDropdown.sendKeys(Keys.TAB);
        return this;
    }

    public ChangeModelUnderTestPage enterOtherModelField(String modelType) {
        FormDataHelper.enterText(vehicleModelOtherField, modelType);
        return this;
    }

    public StartTestConfirmationPage submit() {
        submit.click();
        return new StartTestConfirmationPage(driver);
    }
}
