package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAEDetailsNamePage extends ChangeAEDetailsPage {
    public static final String PATH = "/authorised-examiner/%s/name/change";
    public static final String PAGE_TITLE = "Change business name";

    @FindBy(id = "aeNameTextBox") private WebElement aEName;

    public ChangeAEDetailsNamePage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAEDetailsNamePage inputName(String name) {
        FormDataHelper.enterText(aEName, name);
        return this;
    }
}
