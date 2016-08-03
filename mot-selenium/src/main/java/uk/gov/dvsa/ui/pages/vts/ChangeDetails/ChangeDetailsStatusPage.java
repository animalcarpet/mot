package uk.gov.dvsa.ui.pages.vts.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.domain.model.site.Status;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeDetailsStatusPage extends ChangeDetailsPage {
    public static final String PATH = "/vehicle-testing-station/%s/status/change";
    public static final String PAGE_TITLE = "Change status";

    @FindBy(id = "vtsStatusSelectSet") private WebElement statusSelect;

    public ChangeDetailsStatusPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeDetailsStatusPage changeSiteStatus(Status newStatus) {
        FormDataHelper.selectFromDropDownByVisibleText(statusSelect, newStatus.getText());
        return this;
    }
}
