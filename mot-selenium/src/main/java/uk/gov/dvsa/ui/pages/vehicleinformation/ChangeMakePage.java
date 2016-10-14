package uk.gov.dvsa.ui.pages.vehicleinformation;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeMakePage extends Page {

    public static final String PATH = "/change/make";
    private static final String PAGE_TITLE = "Change make and model";

    @FindBy(id = "vehicleMake") WebElement makeDropdown;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeMakePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeMakePage selectMake(Make make) {
        FormDataHelper.selectFromDropDownByValue(makeDropdown, make.getId().toString());
        return this;
    }

    public ChangeModelPage submit() {
        submit.click();
        return new ChangeModelPage(driver);
    }
}
