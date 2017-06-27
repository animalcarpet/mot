package uk.gov.dvsa.ui.pages.error;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class PageNotFoundPage extends Page {

    private static final String PAGE_TITLE = "This page cannot be found";

    public PageNotFoundPage(MotAppDriver driver) {
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
