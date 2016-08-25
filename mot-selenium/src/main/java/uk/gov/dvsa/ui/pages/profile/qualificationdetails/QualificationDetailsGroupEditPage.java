package uk.gov.dvsa.ui.pages.profile.qualificationdetails;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;

public class QualificationDetailsGroupEditPage extends AbstractQualificationDetailsGroupPage {

    private static final String PAGE_TITLE = "Change a certificate";

    public QualificationDetailsGroupEditPage(MotAppDriver driver) {
        super(driver, PAGE_TITLE);
    }
}
