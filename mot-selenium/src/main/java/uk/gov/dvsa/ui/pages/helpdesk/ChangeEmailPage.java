package uk.gov.dvsa.ui.pages.helpdesk;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.FormDataHelper;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeEmailPage extends Page{

    private static final String PAGE_TITLE = "Change email address";

    @FindBy (id = "email") private WebElement emailAddressInput;
    @FindBy (id = "emailConfirm") private WebElement confirmEmailInput;
    @FindBy (id = "submitEmailAddress") private WebElement submitEmailButton;

    public ChangeEmailPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    public boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public HelpDeskUserProfilePage updateEmailSuccessfully(String email) {
        FormDataHelper.enterText(emailAddressInput, email);
        FormDataHelper.enterText(confirmEmailInput, email);
        submitEmailButton.click();
        return new HelpDeskUserProfilePage(driver);
    }
}
