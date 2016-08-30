package uk.gov.dvsa.ui.pages.mot;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.mot.Defect;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class AddAManualAdvisoryPage extends DefectsPage {

    private static final String PAGE_TITLE = "Add a manual advisory";

    @FindBy(id = "comment") private WebElement defectDescription;

    public AddAManualAdvisoryPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AddAManualAdvisoryPage fillDefectDescription(Defect defect) {
        defectDescription.sendKeys(defect.getDescription());

        return this;
    }

    public AddAManualAdvisoryPage clickAddDefectButtonExpectingFailure() {
        addDefect.click();

        return this;
    }
}
