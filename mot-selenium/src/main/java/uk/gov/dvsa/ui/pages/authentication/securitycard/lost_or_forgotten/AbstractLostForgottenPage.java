package uk.gov.dvsa.ui.pages.authentication.securitycard.lost_or_forgotten;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

class AbstractLostForgottenPage extends Page {
    private String page_title = " ";

    public AbstractLostForgottenPage(MotAppDriver driver, String page_title) {
        super(driver);
        this.page_title = page_title;
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), page_title)
            && PageInteractionHelper.elementNotExpectedOnPage(headerUserName, "User Name")
            && PageInteractionHelper.elementNotExpectedOnPage(headerMenu, "[Home, Your Profile, Sign out]");
    }
}
