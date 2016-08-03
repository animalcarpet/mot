package uk.gov.dvsa.ui.pages.authorisedexaminer.ChangeDetails;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;

public class ChangeAECorrespondenceEmailPage extends ChangeAEDetailsPage {
    public static final String PAGE_TITLE = "Change correspondence email address";
    public static final String PATH = "/authorised-examiner/%s/correspondence-email/change";

    @FindBy (id = "email") private WebElement emailField;

    public ChangeAECorrespondenceEmailPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }

    public ChangeAECorrespondenceEmailPage inputContactDetailsEmail(String newEmail) {
        FormDataHelper.enterText(emailField, newEmail);
        return this;
    }
}