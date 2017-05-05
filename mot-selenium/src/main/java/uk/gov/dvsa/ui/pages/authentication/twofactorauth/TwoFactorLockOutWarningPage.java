package uk.gov.dvsa.ui.pages.authentication.twofactorauth;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.interfaces.WarningPage;
import uk.gov.dvsa.ui.pages.Page;

public class TwoFactorLockOutWarningPage extends Page implements WarningPage {

    private static final String PAGE_TITLE = "Sign in";
    private static final String LOCKOUT_WARNING_MESSAGE = "One PIN attempt left";

    @FindBy(className = "banner--error") private WebElement warningMessage;

    public TwoFactorLockOutWarningPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
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