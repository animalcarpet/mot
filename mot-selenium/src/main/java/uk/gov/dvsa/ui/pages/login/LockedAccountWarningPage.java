package uk.gov.dvsa.ui.pages.login;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.interfaces.WarningPage;
import uk.gov.dvsa.ui.pages.Page;

public class LockedAccountWarningPage extends Page implements WarningPage{

    private static final String PAGE_TITLE = "Your account is locked";
    private static final String LOCKOUT_WARNING_MESSAGE = "You have tried to sign in too many times. Try again in 30 min or reset your password.";
    @FindBy(className = "lede") private WebElement warningMessage;

    public LockedAccountWarningPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean isMessageDisplayed() {
        return warningMessage.getText().contains(LOCKOUT_WARNING_MESSAGE);
    }
}
