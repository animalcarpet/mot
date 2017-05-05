package uk.gov.dvsa.ui.pages.authentication.twofactorauth;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.interfaces.WarningPage;
import uk.gov.dvsa.ui.pages.Page;

public class TwoFactorLockedAccountWarningPage extends Page implements WarningPage{

    private static final String PAGE_TITLE = "Your account is locked";
    private static final String LOCKOUT_WARNING_MESSAGE = "Account locked for 5 minutes";

    @FindBy(className = "banner--error") private WebElement warningMessage;

    public TwoFactorLockedAccountWarningPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    @Override
    public boolean isMessageDisplayed() {
        return warningMessage.getText().contains(LOCKOUT_WARNING_MESSAGE);
    }
}