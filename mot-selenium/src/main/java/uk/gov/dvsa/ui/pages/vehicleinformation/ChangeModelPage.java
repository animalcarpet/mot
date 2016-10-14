package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Model;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeModelPage extends Page {

    public static final String PATH = "/change/model";
    private static final String PAGE_TITLE = "Change make and model";

    @FindBy(id = "vehicleModel") WebElement modelDropdown;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeModelPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeModelPage selectModel(Model model) {
        FormDataHelper.selectFromDropDownByValue(modelDropdown, model.getId().toString());
        return this;
    }

    public ChangeMakeModelReviewPage submit() {
        submit.click();
        return new ChangeMakeModelReviewPage(driver);
    }
}
