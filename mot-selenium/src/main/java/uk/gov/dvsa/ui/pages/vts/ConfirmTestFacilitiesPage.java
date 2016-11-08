package uk.gov.dvsa.ui.pages.vts;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;


public class ConfirmTestFacilitiesPage extends Page {
    private static final String PAGE_TITLE = "Review change to testing facilities";
    @FindBy(id = "submitTestingFacilitiesUpdate") private WebElement saveTestingFacilitiesChangesButton;

    public ConfirmTestFacilitiesPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public VehicleTestingStationPage clickOnConfirmButton() {
        saveTestingFacilitiesChangesButton.click();
        return new VehicleTestingStationPage(driver);
    }
}
