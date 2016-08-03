package uk.gov.dvsa.ui.pages.profile.annualassessmentcertificates;

import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import uk.gov.dvsa.framework.config.webdriver.MotAppDriver;
import uk.gov.dvsa.helper.PageInteractionHelper;
import uk.gov.dvsa.ui.pages.Page;

public class AnnualAssessmentCertificatesReviewPage extends Page {

    private static final String PAGE_TITLE = "Review your assessment certificate";
    @FindBy(id ="confirm-button")
    private WebElement confirm;

    public AnnualAssessmentCertificatesReviewPage(MotAppDriver driver) {
        super(driver);
    }

    @Override
    protected boolean selfVerify() {
        return PageInteractionHelper.verifyTitle(this.getTitle(), PAGE_TITLE);
    }

    public AnnualAssessmentCertificatesIndexPage confirmAndGoToIndexPage() {
        confirm.click();
        return new AnnualAssessmentCertificatesIndexPage(driver);
    }
}
