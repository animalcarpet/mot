package uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates;

import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;

public class AnnualAssessmentCertificatesEditPage extends AnnualAssessmentCertificatesAddPage {

    private static final String PAGE_TITLE = "Change your group B assessment certificate";

    public AnnualAssessmentCertificatesEditPage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

}
