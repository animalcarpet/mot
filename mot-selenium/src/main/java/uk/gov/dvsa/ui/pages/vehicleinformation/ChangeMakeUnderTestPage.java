package uk.gov.dvsa.ui.pages.vehicleinformation;


import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.vehicle.Make;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeMakeUnderTestPage extends Page {

    private static final String PAGE_TITLE = "What is the vehicle's make?";

    @FindBy(id = "vehicleMake") WebElement vehicleMakeDropdown;
    @FindBy(id = "otherMake") WebElement vehicleMakeOtherField;
    @FindBy(id = "submitUpdate") WebElement submit;

    public ChangeMakeUnderTestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ChangeMakeUnderTestPage selectMake(Make make) {
        FormDataHelper.selectFromDropDownByValue(vehicleMakeDropdown, make.getId().toString());
        return this;
    }

    public ChangeMakeUnderTestPage selectMakeOther() {
        FormDataHelper.selectFromDropDownByValue(vehicleMakeDropdown, "other");
        return this;
    }

    public ChangeMakeUnderTestPage enterOtherMakeField(String makeType) {
        FormDataHelper.enterText(vehicleMakeOtherField, makeType);
        return this;
    }

    public ChangeModelUnderTestPage submit() {
        submit.click();
        return new ChangeModelUnderTestPage(driver);
    }

}