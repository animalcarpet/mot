package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.AEAuthStatus;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAEDetailsStatusPage extends ChangeAEDetailsPage {
    public static final String PATH = "/authorised-examiner/%s/status/change";
    public static final String PAGE_TITLE = "Change status";

    @FindBy(id = "aeStatusSelectSet") private WebElement statusSelect;

    public ChangeAEDetailsStatusPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAEDetailsStatusPage changeStatus(AEAuthStatus newStatus) {
        FormDataHelper.selectFromDropDownByVisibleText(statusSelect, newStatus.getText());
        return this;
    }
}
