package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAERegOfficeEmailPage extends ChangeAEDetailsPage {
    public static final String PAGE_TITLE = "Change registered office email address";
    public static final String PATH = "/authorised-examiner/%s/registered-email/change";

    @FindBy (id = "email") private WebElement emailField;

    public ChangeAERegOfficeEmailPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAERegOfficeEmailPage inputContactDetailsEmail(String newEmail) {
        FormDataHelper.enterText(emailField, newEmail);
        return this;
    }
}