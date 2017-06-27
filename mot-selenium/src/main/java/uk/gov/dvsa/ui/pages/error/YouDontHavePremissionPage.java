package uk.gov.dvsa.ui.pages.error;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class YouDontHavePremissionPage extends Page {

    private static final String PAGE_TITLE = "You don't have permission";

    public YouDontHavePremissionPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public boolean isErrorMessageDisplayed(){
        return selfVerify();
    }
}
