package uk.gov.dvsa.ui.pages.mot.retest;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ConfirmVehicleRetestPage extends Page{

    private static final String PAGE_TITLE = "Confirm vehicle for retest";

    @FindBy(id = "retest_vehicle_confirmation") private WebElement startRetestButton;

    @FindBy(id = "vehicleVINnumber") private  WebElement vinText;

    @FindBy(id = "vehicle-search-retest") private WebElement forReTestLable;

    public ConfirmVehicleRetestPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public ReTestOptionsPage startRetest(){
        startRetestButton.click();

        return new ReTestOptionsPage(driver);
    }

    public ReTestResultsEntryPage startContigencyRetest(){
        startRetestButton.click();

        return new ReTestResultsEntryPage(driver);
    }

    public String getVIN() {
        return vinText.getText();
    }

    public boolean isForTestLabelDisplayed() {
        return forReTestLable.isDisplayed();
    }
}