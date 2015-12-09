package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.interfaces.DisplayMessage;
import uk.gov.dvsa.ui.pages.Page;

public class LockOutWarningPage extends Page implements DisplayMessage {

    private static final String PAGE_TITLE = "Authentication failed";
    private static final String LOCKOUT_WARNING_MESSAGE = "Your account will be locked";

    @FindBy(linkText = "change your password") private WebElement changePasswordLink;
    @FindBy(className = "lede") private WebElement warningMessage;

    public LockOutWarningPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(warningMessage.getText(), LOCKOUT_WARNING_MESSAGE);
    }

    @Override
    public boolean isMessageDisplayed() {
        return warningMessage.getText().contains(LOCKOUT_WARNING_MESSAGE);
    }
}
