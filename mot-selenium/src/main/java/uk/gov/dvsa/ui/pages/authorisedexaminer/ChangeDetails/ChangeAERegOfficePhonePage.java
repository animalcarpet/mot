package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAERegOfficePhonePage extends ChangeAEDetailsPage {
    public static final String PAGE_TITLE = "Change registered office telephone number";
    public static final String PATH = "/authorised-examiner/%s/registered-telephone/change";

    @FindBy (id = "phoneTextBox") private WebElement phoneField;

    public ChangeAERegOfficePhonePage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAERegOfficePhonePage inputTelephone(String newPhone) {
        FormDataHelper.enterText(phoneField, newPhone);
        return this;
    }
}