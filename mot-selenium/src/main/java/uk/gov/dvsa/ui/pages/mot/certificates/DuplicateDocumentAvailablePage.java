package uk.gov.dvsa.ui.pages.mot.certificates;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class DuplicateDocumentAvailablePage extends Page {

    private static final String PAGE_TITLE = "Duplicate document available";

    public DuplicateDocumentAvailablePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }
}

