package uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AnnualAssessmentCertificatesRemovePage extends Page {

    @FindBy (id = "confirm-button") private WebElement submit;

    private static final String PAGE_TITLE = "Remove your assessment certificate";

    public AnnualAssessmentCertificatesRemovePage(MotAppDriver driver) {
        super(driver);
        selfVerify();
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AnnualAssessmentCertificatesIndexPage submitAndGoToIndexPage(){
        submit.click();
        return new AnnualAssessmentCertificatesIndexPage(driver);
    }

}
