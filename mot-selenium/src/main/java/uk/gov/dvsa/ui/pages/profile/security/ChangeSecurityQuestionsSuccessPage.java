package uk.gov.dvsa.ui.pages.profile.security;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class ChangeSecurityQuestionsSuccessPage extends Page {
    private static final String PAGE_TITLE ="Your security questions have been changed";

    public ChangeSecurityQuestionsSuccessPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(getTitle(), PAGE_TITLE);
    }

    public String successMessage() {
        return getTitle();
    }
}
