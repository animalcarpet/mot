package uk.gov.dvsa.ui.pages.profile.qualificationdetails;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class QualificationDetailsGroupAddPage extends AbstractQualificationDetailsGroupPage {

    private static final String PAGE_TITLE = "Add a certificate";

    public QualificationDetailsGroupAddPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }
}
