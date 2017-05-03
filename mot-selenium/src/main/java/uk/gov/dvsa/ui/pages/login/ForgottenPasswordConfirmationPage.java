package uk.gov.dvsa.ui.pages.login;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class ForgottenPasswordConfirmationPage extends AbstractForgottenPasswordPage {
    private static final String PAGE_TITLE = "Security questions answered correctly";
    public static final String PATH = "/forgotten-password/confirmation-email";

    public ForgottenPasswordConfirmationPage(final MotAppDriver driver) {
        super(driver, PAGE_TITLE);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }
}
